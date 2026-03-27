<?php

// Quick FCM test script
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing FCM Setup...\n\n";

// 1. Check if service account file exists
$credPath = storage_path('app/firebase-service-account.json');
echo "1. Checking service account file: $credPath\n";
if (file_exists($credPath)) {
    echo "   ✓ File exists\n";
    $cred = json_decode(file_get_contents($credPath), true);
    echo "   Project ID: " . ($cred['project_id'] ?? 'NOT FOUND') . "\n";
    echo "   Client Email: " . ($cred['client_email'] ?? 'NOT FOUND') . "\n";
} else {
    echo "   ✗ FILE NOT FOUND!\n";
    echo "   Upload firebase-service-account.json to: $credPath\n";
    exit(1);
}

// 2. Check config
echo "\n2. Checking config:\n";
echo "   FIREBASE_PROJECT_ID: " . config('services.firebase.project_id') . "\n";
echo "   FIREBASE_CREDENTIALS: " . config('services.firebase.credentials') . "\n";

// 3. Check if device has FCM token
echo "\n3. Checking devices with FCM tokens:\n";
$devices = \App\Models\Device::whereNotNull('fcm_token')->get();
echo "   Found " . $devices->count() . " devices with FCM tokens\n";
foreach ($devices as $device) {
    echo "   - Device #{$device->id}: {$device->imei} (Token: " . substr($device->fcm_token, 0, 20) . "...)\n";
}

// 4. Test sending FCM
if ($devices->count() > 0) {
    $device = $devices->first();
    echo "\n4. Testing FCM send to device #{$device->id}:\n";
    
    try {
        $service = new \App\Services\DeviceCommandService();
        $command = $service->sendCommand($device, 'lock', ['reason' => 'Test from script']);
        echo "   ✓ Command created: #{$command->id}\n";
        echo "   Status: {$command->status}\n";
        echo "\n   Now run: php artisan queue:work\n";
        echo "   Or check: php artisan queue:failed\n";
    } catch (\Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
