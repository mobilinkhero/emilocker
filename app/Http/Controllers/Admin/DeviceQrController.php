<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\QrProvisioningService;
use Illuminate\Support\Facades\Auth;

class DeviceQrController extends Controller
{
    public function show(int $deviceId)
    {
        // Only authenticated Filament admins
        if (! Auth::guard('web')->check()) {
            abort(403);
        }

        $device  = Device::findOrFail($deviceId);
        $qrData  = app(QrProvisioningService::class)->generateForDevice($device);
        $qrJson  = json_encode($qrData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Generate QR image using simplesoftwareio/simple-qrcode
        $qrImage = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrJson);

        return view('admin.device-qr', compact('device', 'qrData', 'qrJson', 'qrImage'));
    }
}
