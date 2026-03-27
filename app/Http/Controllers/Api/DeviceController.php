<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Services\DeviceCommandService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(private DeviceCommandService $commandService) {}

    /**
     * Called by Android app on first boot after QR provisioning.
     * Authenticates via device_api_key embedded in QR — no retailer token needed.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'device_api_key'  => 'required|string',
            'imei'            => 'required|string',
            'imei2'           => 'nullable|string',
            'model'           => 'required|string',
            'brand'           => 'required|string',
            'android_version' => 'required|string',
            'fcm_token'       => 'required|string',
            'sim_serial'      => 'nullable|string',
        ]);

        // Find the pre-registered device slot by API key
        $device = Device::where('api_key', $data['device_api_key'])->first();

        if (! $device) {
            return response()->json([
                'error' => 'Invalid device API key. Create a device slot in the admin panel first.',
            ], 401);
        }

        // Update with real device info
        $device->update([
            'imei'            => $data['imei'],
            'imei2'           => $data['imei2'] ?? null,
            'model'           => $data['model'],
            'brand'           => $data['brand'],
            'android_version' => $data['android_version'],
            'fcm_token'       => $data['fcm_token'],
            'sim_serial'      => $data['sim_serial'] ?? null,
            'registered_at'   => now(),
            'status'          => 'locked', // stays locked until EMI plan assigned
        ]);

        DeviceEvent::create([
            'device_id'   => $device->id,
            'event_type'  => 'registered',
            'payload'     => ['imei' => $device->imei, 'model' => $device->model],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'device_id' => $device->id,
            'status'    => $device->status,
            'message'   => 'Device registered successfully.',
        ], 200);
    }

    /**
     * Device heartbeat — called every few minutes by the app.
     */
    public function heartbeat(Request $request, string $imei)
    {
        $device = Device::where('imei', $imei)->firstOrFail();

        $device->update([
            'is_online'    => true,
            'last_seen_at' => now(),
            'fcm_token'    => $request->fcm_token ?? $device->fcm_token,
            'battery_level'=> $request->battery_level,
            'storage_used' => $request->storage_used,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
        ]);

        // Return pending commands to the device
        $pendingCommands = $device->commands()
            ->where('status', 'pending')
            ->get(['id', 'command', 'payload']);

        return response()->json([
            'device_status'   => $device->status,
            'pending_commands'=> $pendingCommands,
        ]);
    }

    /**
     * Device reports a security event (SIM change, root, etc.)
     */
    public function reportEvent(Request $request, string $imei)
    {
        $device = Device::where('imei', $imei)->firstOrFail();

        $event = $request->validate([
            'event_type' => 'required|string',
            'payload'    => 'nullable|array',
        ]);

        DeviceEvent::create([
            'device_id'   => $device->id,
            'event_type'  => $event['event_type'],
            'payload'     => $event['payload'] ?? [],
            'occurred_at' => now(),
        ]);

        // Auto-lock on SIM change or root detection
        if (in_array($event['event_type'], ['sim_change', 'root_detected', 'factory_reset_attempt'])) {
            $this->commandService->sendCommand($device, 'lock', [
                'reason' => $event['event_type'],
            ]);
            $device->update(['status' => 'locked', 'lock_reason' => $event['event_type']]);
        }

        return response()->json(['message' => 'Event recorded.']);
    }

    /**
     * Retailer/Admin: send command to device
     */
    public function sendCommand(Request $request, string $imei)
    {
        $device = Device::where('imei', $imei)->firstOrFail();

        $data = $request->validate([
            'command' => 'required|in:lock,unlock,kiosk,wipe,message,update_config',
            'payload' => 'nullable|array',
        ]);

        $command = $this->commandService->sendCommand($device, $data['command'], $data['payload'] ?? []);

        return response()->json(['command_id' => $command->id, 'status' => 'queued']);
    }

    /**
     * Device acknowledges command execution
     */
    public function ackCommand(Request $request, int $commandId)
    {
        $command = \App\Models\DeviceCommand::findOrFail($commandId);
        $command->update(['status' => 'executed', 'executed_at' => now()]);

        return response()->json(['message' => 'Acknowledged.']);
    }

    public function status(string $imei)
    {
        $device = Device::with(['emiPlan', 'customer'])->where('imei', $imei)->firstOrFail();

        return response()->json([
            'status'       => $device->status,
            'lock_reason'  => $device->lock_reason,
            'is_online'    => $device->is_online,
            'last_seen_at' => $device->last_seen_at,
            'emi_status'   => $device->emiPlan?->status,
            'next_due_date'=> $device->emiPlan?->next_due_date,
        ]);
    }
}
