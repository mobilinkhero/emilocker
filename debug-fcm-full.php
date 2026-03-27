<?php

// Comprehensive FCM debugging script
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE FCM DEBUG ===\n\n";

// 1. Check Firebase config
echo "1. FIREBASE CONFIGURATION:\n";
$credPath = storage_path('app/firebase-service-account.json');
if (file_exists($credPath)) {
    $cred = json_decode(file_get_contents($credPath), true);
    $fileProjectId = $cred['project_id'] ?? 'NOT FOUND';
    $configProjectId = config('services.firebase.project_id');
    
    echo "   File Project ID: $fileProjectId\n";
    echo "   Config Project ID: $configProjectId\n";
    
    if ($fileProjectId !== $configProjectId) {
        echo "   ❌ MISMATCH! Update .env FIREBASE_PROJECT_ID to: $fileProjectId\n";
    } else {
        echo "   ✓ Project IDs match\n";
    }
} else {
    echo "   ❌ Service account file not found!\n";
    exit(1);
}

// 2. Check queue connection
echo "\n2. QUEUE CONFIGURATION:\n";
echo "   Queue Driver: " . config('queue.default') . "\n";
echo "   Queue Connection: " . config('queue.connections.database.driver') . "\n";

// 3. Check pending jobs
echo "\n3. QUEUE STATUS:\n";
$pendingJobs = \DB::table('jobs')->count();
$failedJobs = \DB::table('failed_jobs')->count();
echo "   Pending jobs: $pendingJobs\n";
echo "   Failed jobs: $failedJobs\n";

if ($failedJobs > 0) {
    echo "\n   Recent failed jobs:\n";
    $failed = \DB::table('failed_jobs')->latest()->limit(3)->get();
    foreach ($failed as $job) {
        echo "   - ID: {$job->id}, Failed at: {$job->failed_at}\n";
        $payload = json_decode($job->payload, true);
        echo "     Command: " . ($payload['displayName'] ?? 'Unknown') . "\n";
        echo "     Exception: " . substr($job->exception, 0, 200) . "...\n\n";
    }
}

// 4. Check devices with FCM tokens
echo "\n4. DEVICES WITH FCM TOKENS:\n";
$devices = \App\Models\Device::whereNotNull('fcm_token')
    ->where('fcm_token', '!=', 'pending')
    ->where('fcm_token', '!=', 'test')
    ->get();

if ($devices->count() === 0) {
    echo "   ❌ No devices with valid FCM tokens!\n";
    exit(1);
}

echo "   Found {$devices->count()} device(s):\n";
foreach ($devices as $device) {
    echo "   - Device #{$device->id}: {$device->imei}\n";
    echo "     FCM Token: " . substr($device->fcm_token, 0, 30) . "...\n";
    echo "     Status: {$device->status}\n";
    echo "     Last Seen: " . ($device->last_seen_at ?? 'Never') . "\n";
}

// 5. Test sending FCM to first device
$device = $devices->first();
echo "\n5. TESTING FCM SEND TO DEVICE #{$device->id}:\n";

try {
    // Create command
    $service = new \App\Services\DeviceCommandService();
    $command = $service->sendCommand($device, 'lock', ['reason' => 'Debug test from script']);
    
    echo "   ✓ Command created: #{$command->id}\n";
    echo "   Status: {$command->status}\n";
    
    // Check if job was queued
    $jobCount = \DB::table('jobs')->where('id', '>=', \DB::table('jobs')->max('id'))->count();
    echo "   Jobs in queue: $jobCount\n";
    
    if ($jobCount > 0) {
        echo "\n   ⚠️  Job is queued but not processed!\n";
        echo "   Queue worker is NOT running!\n";
        echo "\n   Start it with:\n";
        echo "   php artisan queue:work\n";
        echo "   OR\n";
        echo "   sudo supervisorctl start laravel-worker:*\n";
    } else {
        echo "\n   ✓ Job was processed (or queue is sync)\n";
        
        // Check command status
        $command->refresh();
        echo "   Command status after queue: {$command->status}\n";
        
        if ($command->status === 'delivered') {
            echo "   ✓ FCM sent successfully!\n";
        } else {
            echo "   ❌ FCM not delivered. Check logs.\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   " . $e->getTraceAsString() . "\n";
}

// 6. Check recent commands
echo "\n6. RECENT COMMANDS (Last 5):\n";
$commands = \App\Models\DeviceCommand::with('device')
    ->latest()
    ->limit(5)
    ->get();

foreach ($commands as $cmd) {
    echo "   - Command #{$cmd->id}: {$cmd->command}\n";
    echo "     Device: #{$cmd->device_id} ({$cmd->device->imei})\n";
    echo "     Status: {$cmd->status}\n";
    echo "     Created: {$cmd->created_at}\n";
    if ($cmd->delivered_at) {
        echo "     Delivered: {$cmd->delivered_at}\n";
    }
    echo "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
echo "\nNEXT STEPS:\n";
echo "1. If queue worker not running: Start it\n";
echo "2. If FCM not delivered: Check Laravel logs\n";
echo "3. If device not receiving: Check device logs with adb logcat\n";
echo "4. Test from admin panel and watch both server and device logs\n";
