<?php

namespace App\Jobs;

use App\Models\DeviceCommand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFcmCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private string $fcmToken,
        private DeviceCommand $command,
    ) {}

    public function handle(): void
    {
        $projectId   = config('services.firebase.project_id');
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $this->fcmToken,
                    'data'  => [
                        'command_id' => (string) $this->command->id,
                        'command'    => $this->command->command,
                        'payload'    => json_encode($this->command->payload ?? []),
                    ],
                    'android' => [
                        'priority' => 'high',
                        'ttl'      => '86400s',
                    ],
                ],
            ]);

        if ($response->successful()) {
            $this->command->update(['status' => 'delivered', 'delivered_at' => now()]);
            Log::info("FCM V1 delivered command #{$this->command->id} ({$this->command->command})");
        } else {
            Log::error('FCM V1 send failed', [
                'status'     => $response->status(),
                'body'       => $response->body(),
                'command_id' => $this->command->id,
            ]);
            $this->fail("FCM V1 delivery failed: " . $response->body());
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
            $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim  = base64url_encode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $unsignedJwt = "{$header}.{$claim}";
            openssl_sign($unsignedJwt, $signature, $credentials['private_key'], 'SHA256');
            $jwt = $unsignedJwt . '.' . base64url_encode($signature);

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
}

if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
