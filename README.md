# NoxLock EMI MDM — Laravel Backend

## Quick Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan make:filament-user   # creates super admin
php artisan storage:link
php artisan serve
```

## Queue Worker (required for FCM commands + reminders)
```bash
php artisan queue:work redis --queue=commands,notifications,default
```

## Scheduler (required for auto-lock + reminders)
```bash
# Add to crontab:
* * * * * cd /path/to/backend_larvel && php artisan schedule:run >> /dev/null 2>&1
```

---

## Panels
| Panel | URL | Auth |
|---|---|---|
| Admin (Filament) | /admin | Super admin user |
| Customer Portal | /customer/dashboard | Customer phone/password |
| Landing Page | / | Public |

---

## Android App Testing Guide

### Phase 1 — Test without Device Owner (safe, no factory reset needed)
1. Build debug APK: `./gradlew assembleDebug`
2. Sideload: `adb install app/build/outputs/apk/debug/app-debug.apk`
3. Open app → it will show DebugActivity (debug builds only)
4. Tap "Start Heartbeat Service" → connects to your local Laravel (`10.0.2.2:8000`)
5. Tap "Simulate LOCK" → LockScreenActivity appears
6. Tap "Simulate UNLOCK" → lock screen dismissed
7. Test FCM by sending a test message from Firebase Console

### Phase 2 — Test Device Owner on a spare/test device
1. Factory reset a cheap test device (keep one dedicated for this)
2. On the setup wizard, tap the screen 6 times on the "Welcome" screen to enter provisioning mode
3. Scan the QR code generated from `/admin/devices/{id}/qr`
4. App installs as Device Owner automatically
5. Verify: `adb shell dpm list-owners` should show your package

### Phase 3 — Production
- Build release APK signed with your keystore
- Upload to `/admin/app-release` in Filament panel
- All devices will auto-update within 6 hours via OTA

---

## QR Provisioning JSON Structure
```json
{
  "android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME": "com.noxlock.emi.locker/.receiver.DeviceAdminReceiver",
  "android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION": "https://yourdomain.com/storage/releases/noxlock-latest.apk",
  "android.app.extra.PROVISIONING_DEVICE_ADMIN_SIGNATURE_CHECKSUM": "<SHA256_OF_APK>",
  "android.app.extra.PROVISIONING_SKIP_ENCRYPTION": false,
  "android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE": {
    "device_api_key": "DEVICE_API_KEY_HERE",
    "api_base_url": "https://yourdomain.com",
    "device_id": 123
  }
}
```

## Anti-Bypass Summary
| Attack | Defense |
|---|---|
| Factory reset | `DISALLOW_FACTORY_RESET` via Device Owner |
| Safe mode | `DISALLOW_SAFE_BOOT` via Device Owner |
| Uninstall app | `setUninstallBlocked(true)` via Device Owner |
| SIM swap | `SimChangeReceiver` → instant lock + backend report |
| Root | `SecurityChecker.isRooted()` → lock + report |
| No internet | Offline lock after 8 hours |
| ADB debug | `DISALLOW_DEBUGGING_FEATURES` via Device Owner |
| Settings access | `setApplicationHidden(settings, true)` |
