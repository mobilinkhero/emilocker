<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>

            <x-filament::button 
                type="button" 
                color="gray"
                wire:click="testConnection"
            >
                Test Connection
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Current Configuration
        </x-slot>

        <div class="space-y-2 text-sm">
            <div>
                <span class="font-semibold">Project ID:</span>
                <span class="text-gray-600 dark:text-gray-400">
                    {{ config('services.firebase.project_id') ?: 'Not configured' }}
                </span>
            </div>
            <div>
                <span class="font-semibold">Credentials File:</span>
                <span class="text-gray-600 dark:text-gray-400">
                    @if(file_exists(storage_path(config('services.firebase.credentials'))))
                        <span class="text-green-600">✓ File exists</span>
                    @else
                        <span class="text-red-600">✗ File not found</span>
                    @endif
                </span>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            How to Get Firebase Service Account
        </x-slot>

        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li>Go to <a href="https://console.firebase.google.com" target="_blank" class="text-primary-600 hover:underline">Firebase Console</a></li>
            <li>Select your project</li>
            <li>Click the gear icon → Project Settings</li>
            <li>Go to "Service Accounts" tab</li>
            <li>Click "Generate New Private Key"</li>
            <li>Download the JSON file and upload it here</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
