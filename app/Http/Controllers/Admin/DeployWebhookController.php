<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * GitHub webhook — auto-deploys on push to main.
 * Pure PHP/Laravel, no shell scripts needed.
 *
 * Webhook URL: https://yourdomain.com/webhook/deploy
 * Secret: DEPLOY_WEBHOOK_SECRET in .env
 */
class DeployWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify GitHub signature
        $secret    = env('DEPLOY_WEBHOOK_SECRET');
        $signature = $request->header('X-Hub-Signature-256');

        if ($secret && $signature) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, $signature)) {
                Log::warning('Deploy webhook: invalid signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        // 2. Only deploy on push to main
        $payload = $request->json()->all();
        $ref     = $payload['ref'] ?? '';

        if ($ref !== 'refs/heads/main') {
            return response()->json(['message' => "Ignored: {$ref}"]);
        }

        $commit = $payload['head_commit']['message'] ?? 'unknown';
        $pusher = $payload['pusher']['name'] ?? 'unknown';

        Log::info("Deploy triggered by {$pusher}: {$commit}");

        // 3. Git pull
        $output = [];
        exec('git -C ' . base_path() . ' pull origin main 2>&1', $output);
        Log::info('git pull: ' . implode("\n", $output));

        // 4. Composer install
        exec('composer install --no-dev --optimize-autoloader --no-interaction -d ' . base_path() . ' 2>&1', $output);

        // 5. Run migrations
        Artisan::call('migrate', ['--force' => true]);

        // 6. Clear and rebuild caches
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('filament:upgrade');

        // 7. Restart queue workers
        Artisan::call('queue:restart');

        $log = implode("\n", $output);
        Log::info("Deploy completed for commit: {$commit}");

        // Save deploy log
        file_put_contents(
            storage_path('logs/deploy.log'),
            "[" . now() . "] {$pusher}: {$commit}\n{$log}\n\n",
            FILE_APPEND
        );

        return response()->json([
            'message' => 'Deployed successfully',
            'commit'  => $commit,
            'pusher'  => $pusher,
        ]);
    }
}
