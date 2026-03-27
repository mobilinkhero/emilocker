<?php

/**
 * Debug script to verify checksum configuration
 * Run: php debug-checksum.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== NoxLock Checksum Debug ===\n\n";

// 1. Check .env value
echo "1. Raw .env value:\n";
$envChecksum = env('NOXLOCK_APK_CHECKSUM');
echo "   NOXLOCK_APK_CHECKSUM = " . ($envChecksum ?: '(empty)') . "\n";
echo "   Length: " . strlen($envChecksum) . " characters\n\n";

// 2. Check config value
echo "2. Config value (after processing):\n";
$configChecksum = config('noxlock.apk_checksum');
echo "   config('noxlock.apk_checksum') = " . ($configChecksum ?: '(empty)') . "\n";
echo "   Length: " . strlen($configChecksum) . " characters\n\n";

// 3. Check APK file
$apkPath = storage_path('app/public/releases/noxlock-latest.apk');
echo "3. APK file check:\n";
echo "   Path: $apkPath\n";
if (file_exists($apkPath)) {
    echo "   Exists: YES\n";
    echo "   Size: " . number_format(filesize($apkPath)) . " bytes\n";
    echo "   Calculating SHA-256...\n";
    $actualChecksum = hash_file('sha256', $apkPath);
    echo "   Actual checksum (hex): $actualChecksum\n";
    echo "   Length: " . strlen($actualChecksum) . " characters\n";
    
    // Convert to Base64 for comparison
    $bytes = hex2bin($actualChecksum);
    $actualBase64 = base64_encode($bytes);
    $actualUrlSafeBase64 = str_replace(['+', '/', '='], ['-', '_', ''], $actualBase64);
    echo "   Actual checksum (Base64): $actualBase64\n";
    echo "   Actual checksum (URL-Safe Base64): $actualUrlSafeBase64\n\n";
    
    // 4. Compare
    echo "4. Comparison:\n";
    
    // Check if config is hex or base64
    $isHex = ctype_xdigit($configChecksum) && strlen($configChecksum) == 64;
    $isBase64 = !$isHex && strlen($configChecksum) > 0;
    
    if ($isBase64) {
        echo "   Config format: Base64\n";
        if ($configChecksum === $actualBase64 || $configChecksum === $actualUrlSafeBase64) {
            echo "   ✓ MATCH! Config checksum matches APK file.\n";
        } else {
            echo "   ✗ MISMATCH! Checksums do not match.\n";
            echo "   Config (Base64):  $configChecksum\n";
            echo "   Actual (Base64):  $actualBase64\n";
            echo "   Actual (URL-Safe): $actualUrlSafeBase64\n";
        }
    } else if ($isHex) {
        echo "   Config format: Hex\n";
        if ($configChecksum === $actualChecksum) {
            echo "   ✓ MATCH! Config checksum matches APK file.\n";
        } else {
            echo "   ✗ MISMATCH! Checksums do not match.\n";
            echo "   Config:  $configChecksum\n";
            echo "   Actual:  $actualChecksum\n";
        }
    } else {
        echo "   ✗ Config checksum is empty or invalid format!\n";
        echo "   Expected (URL-Safe Base64): $actualUrlSafeBase64\n";
    }
} else {
    echo "   Exists: NO\n";
    echo "   ✗ APK file not found!\n";
}

echo "\n5. QR Provisioning Service check:\n";
$device = \App\Models\Device::first();
if ($device) {
    $qrService = app(\App\Services\QrProvisioningService::class);
    $qrData = $qrService->generateForDevice($device);
    echo "   QR checksum value: " . ($qrData['android.app.extra.PROVISIONING_DEVICE_ADMIN_SIGNATURE_CHECKSUM'] ?? '(empty)') . "\n";
} else {
    echo "   No devices found in database\n";
}

echo "\n=== End Debug ===\n";
