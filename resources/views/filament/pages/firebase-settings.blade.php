<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Firebase Configuration
            </x-slot>
            
            <x-slot name="description">
                Upload your Firebase service account JSON file and configure project settings.
            </x-slot>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Firebase Project ID
                    </label>
                    <input 
                        type="text" 
                        wire:model="projectId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                        placeholder="your-project-id"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Enter your Firebase project ID (e.g., emilockersystem)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Service Account JSON File
                    </label>
                    <input 
                        type="file" 
                        wire:model="serviceAccountFile"
                        accept=".json"
                        class="w-full"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Upload your firebase-service-account.json file from Firebase Console
                    </p>
                    @if($serviceAccountFile)
                        <p class="mt-2 text-sm text-green-600">
                            File ready to upload: {{ $serviceAccountFile->getClientOriginalName() }}
                        </p>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <div class="flex gap-3">
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
