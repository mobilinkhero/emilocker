#!/usr/bin/env php
<?php

/**
 * Test FCM Immediate Send (No Queue Worker Needed)
 * 
 * This script tests the new immediate FCM sending.
 * No queue worker required!
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing IMMEDIATE FCM Send (No Queue Worker)\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Check Firebase config
echo "1. Checking Firebase configuration...\n";
$projectId = config('services.firebase.project_id');
$credPath = config('services.firebase.credentials');
$fullPath = base_path($credPath);

if (!file_exists($fullPath)) {
    echo "✗ Firebase service account file not found!\n";
    echo "  Expected: {$fullPath}\n";
    exit(1);
}

echo "✓ Firebase configured\n";
echo "  Project ID: {$projectId}\n";
echo "  Credentials: {$credPath}\n\n";

// 2. Find a device with FCM token
echo "2. Finding device with FCM token...\n";
$device = \App\Models\Device::whereNotNull('fcm_token')
    ->where('fcm_token', '!=', '')
    ->where('fcm_token', '!=', 'pending...')
    ->first();

if (!$device) {
    echo "✗ No device with valid FCM token found\n";
    exit(1);
}

echo "✓ Found device #{$device->id}\n";
echo "  IMEI: {$device->imei}\n";
echo "  FCM Token: " . substr($device->fcm_token, 0, 30) . "...\n";
echo "  Status: {$device->status}\n";
echo "  Last Seen: {$device->last_seen}\n\n";

// 3. Send test lock command IMMEDIATELY
echo "3. Sending LOCK command (immediate, no queue)...\n";

try {
    $service = new \App\Services\DeviceCommandService();
    $command = $service->sendCommand($device, 'lock', ['reason' => 'Test from immediate script']);
    
    echo "✓ Command created and FCM sent!\n";
    echo "  Command ID: #{$command->id}\n";
    echo "  Status: {$command->status}\n";
    echo "  Command: {$command->command}\n\n";
    
    // Wait a moment and check status
    sleep(2);
    $command->refresh();
    
    echo "4. Checking command status after 2 seconds...\n";
    echo "  Status: {$command->status}\n";
    echo "  Delivered At: " . ($command->delivered_at ?? 'Not yet') . "\n\n";
    
    if ($command->status === 'delivered') {
        echo "✓✓✓ SUCCESS! FCM sent immediately without queue worker!\n";
        echo "\nDevice should be LOCKED now. Check your phone!\n";
    } else {
        echo "⚠ Command created but delivery status: {$command->status}\n";
        echo "Check Laravel logs: tail -f storage/logs/laravel.log\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nCheck logs for details:\n";
    echo "  tail -f storage/logs/laravel.log\n";
    exit(1);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test complete!\n";
echo "\nTo unlock the device, run:\n";
echo "  php artisan tinker\n";
echo "  >>> \$device = \\App\\Models\\Device::find({$device->id});\n";
echo "  >>> app(\\App\\Services\\DeviceCommandService::class)->sendCommand(\$device, 'unlock');\n";
