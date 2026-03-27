<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTestDevice extends Command
{
    protected $signature   = 'noxlock:test-device';
    protected $description = 'Create a test device slot and show the API key';

    public function handle(): void
    {
        $apiKey = Str::random(48);

        $device = Device::create([
            'api_key'        => $apiKey,
            'imei'           => 'TEST_' . Str::random(10),
            'model'          => 'Test Device',
            'brand'          => 'Debug',
            'android_version'=> 'debug',
            'status'         => 'locked',
        ]);

        $this->newLine();
        $this->info("✓ Test device created (ID: {$device->id})");
        $this->newLine();
        $this->line('<fg=yellow>Paste this API key into the NoxLock app:</>');
        $this->line("<fg=cyan>{$apiKey}</>");
        $this->newLine();
        $this->line("Admin panel device page: http://127.0.0.1:8000/admin/devices/{$device->id}");
    }
}
