<?php

return [
    /*
    |--------------------------------------------------------------------------
    | APK Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Android APK deployment and QR provisioning.
    | The checksum is calculated automatically from the APK file.
    |
    */

    // Current APK version
    'apk_version' => env('NOXLOCK_APK_VERSION', '1.0.0'),

    // APK download URL (automatically constructed)
    'apk_url' => env('APP_URL') . '/storage/releases/noxlock-latest.apk',

    /*
    |--------------------------------------------------------------------------
    | Device Configuration
    |--------------------------------------------------------------------------
    */

    // Default device status when created
    'default_device_status' => env('NOXLOCK_DEFAULT_DEVICE_STATUS', 'active'),

    // Auto-lock overdue devices (in hours)
    'auto_lock_after_hours' => env('NOXLOCK_AUTO_LOCK_HOURS', 24),

    // Heartbeat timeout (device considered offline after this many minutes)
    'heartbeat_timeout_minutes' => env('NOXLOCK_HEARTBEAT_TIMEOUT', 10),
];
