<?php

namespace App\Services;

use App\Models\Device;

class QrProvisioningService
{
    /**
     * Generate QR provisioning payload for a pre-registered device.
     * Uses the device's unique API key — embedded in QR, read by SetupActivity.
     */
    public function generateForDevice(Device $device): array
    {
        // Calculate checksum dynamically from the APK file
        $apkPath = storage_path('app/public/releases/noxlock-latest.apk');
        $checksum = $this->calculateApkChecksum($apkPath);
        
        return [
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME' =>
                'com.noxlock.emi.locker/.receiver.DeviceAdminReceiver',

            'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION' =>
                config('app.url') . '/storage/releases/noxlock-latest.apk',

            // Use package checksum calculated from actual APK file
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_CHECKSUM' =>
                $checksum,

            'android.app.extra.PROVISIONING_SKIP_ENCRYPTION' => false,

            'android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE' => [
                'device_api_key' => $device->api_key,
                'api_base_url'   => config('app.url'),
                'device_id'      => $device->id,
            ],
        ];
    }
    
    /**
     * Calculate URL-safe Base64 encoded SHA-256 checksum of APK file.
     */
    private function calculateApkChecksum(string $apkPath): string
    {
        if (!file_exists($apkPath)) {
            throw new \Exception("APK file not found at: $apkPath");
        }
        
        // Calculate SHA-256 hash
        $hash = hash_file('sha256', $apkPath, true);
        
        // Convert to URL-safe Base64
        $base64 = base64_encode($hash);
        $urlSafeBase64 = str_replace(['+', '/', '='], ['-', '_', ''], $base64);
        
        return $urlSafeBase64;
    }
}
