<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviceCommandService
{
    /**
     * Create a command record and send FCM push to device IMMEDIATELY.
     * No queue worker needed - sends synchronously.
     */
    public function sendCommand(Device $device, string $command, array $payload = []): DeviceCommand
    {
        $cmd = DeviceCommand::create([
            'device_id'      => $device->id,
            'issued_by_type' => auth()->check() ? get_class(auth()->user()) : 'System',
            'issued_by_id'   => auth()->id() ?? 0,
            'command'        => $command,
            'payload'        => $payload,
            'status'         => 'pending',
        ]);

        // Send FCM push notification IMMEDIATELY (no queue)
        if ($device->fcm_token) {
            try {
                $this->sendFcmMessage($device->fcm_token, $cmd);
                Log::info("✓ FCM sent immediately for command #{$cmd->id} ({$command})");
            } catch (\Exception $e) {
                Log::error("✗ FCM send failed for command #{$cmd->id}: " . $e->getMessage());
                $cmd->update(['status' => 'failed']);
            }
        } else {
            Log::warning("Device #{$device->id} has no FCM token");
            $cmd->update(['status' => 'failed']);
        }

        return $cmd;
    }

    /**
     * Send FCM message using Firebase Cloud Messaging V1 API.
     */
    private function sendFcmMessage(string $fcmToken, DeviceCommand $command): void
    {
        $projectId   = config('services.firebase.project_id');
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $fcmToken,
                    'data'  => [
                        'command_id' => (string) $command->id,
                        'command'    => $command->command,
                        'payload'    => json_encode($command->payload ?? []),
                    ],
                    'android' => [
                        'priority' => 'high',
                        'ttl'      => '86400s',
                    ],
                ],
            ]);

        if ($response->successful()) {
            $command->update(['status' => 'delivered', 'delivered_at' => now()]);
            Log::info("FCM delivered command #{$command->id} ({$command->command})");
        } else {
            Log::error('FCM send failed', [
                'status'     => $response->status(),
                'body'       => $response->body(),
                'command_id' => $command->id,
            ]);
            throw new \RuntimeException("FCM delivery failed: " . $response->body());
        }
    }

    /**
     * Get a short-lived OAuth2 access token using the service account JSON.
     * Cached for 55 minutes (tokens expire after 60).
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_v1_access_token', 3300, function () {
            $credentialsPath = base_path(config('services.firebase.credentials'));

            if (! file_exists($credentialsPath)) {
                throw new \RuntimeException("Firebase service account file not found at: {$credentialsPath}");
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            // Build JWT assertion
            $now    = time();
            $header = $this->base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim  = $this->base64url_encode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $unsignedJwt = "{$header}.{$claim}";
            openssl_sign($unsignedJwt, $signature, $credentials['private_key'], 'SHA256');
            $jwt = $unsignedJwt . '.' . $this->base64url_encode($signature);

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException("Failed to get FCM access token: " . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Base64 URL-safe encoding.
     */
    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
