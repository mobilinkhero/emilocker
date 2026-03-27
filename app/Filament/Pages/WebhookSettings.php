<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;

class WebhookSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $title           = 'GitHub Auto-Deploy';
    protected static string  $view            = 'filament.pages.webhook-settings';

    public function getWebhookUrl(): string
    {
        return url('/webhook/deploy');
    }

    public function getWebhookSecret(): string
    {
        $secret = env('DEPLOY_WEBHOOK_SECRET', '');
        return $secret ?: 'NOT SET — add DEPLOY_WEBHOOK_SECRET to your .env file';
    }

    public function isSecretSet(): bool
    {
        return !empty(env('DEPLOY_WEBHOOK_SECRET', ''));
    }

    public function getRepoUrl(): string
    {
        return 'https://github.com/mobilinkhero/emilocker';
    }

    public function getLastDeployLog(): string
    {
        $logFile = storage_path('logs/deploy.log');
        if (! File::exists($logFile)) {
            return 'No deployments yet. Set up the webhook and push to main to trigger the first deploy.';
        }
        $lines = array_slice(file($logFile), -60);
        return implode('', $lines);
    }

    public function triggerManualDeploy(): void
    {
        // Simulate a deploy for testing
        $output = [];
        exec('git -C ' . base_path() . ' pull origin main 2>&1', $output);

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        $log = "[" . now() . "] Manual deploy triggered\n" . implode("\n", $output) . "\n\n";
        file_put_contents(storage_path('logs/deploy.log'), $log, FILE_APPEND);

        Notification::make()
            ->title('Manual deploy triggered')
            ->body('Git pull executed. Check the log below.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
