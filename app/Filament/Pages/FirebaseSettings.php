<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class FirebaseSettings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    
    protected static ?string $navigationLabel = 'Firebase Settings';
    
    protected static ?string $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.firebase-settings';

    public $projectId = '';
    public $serviceAccountFile;

    public function mount(): void
    {
        $this->projectId = config('services.firebase.project_id', '');
    }

    public function save(): void
    {
        $this->validate([
            'projectId' => 'required|string',
            'serviceAccountFile' => 'nullable|file|mimes:json|max:1024',
        ]);

        // Handle file upload
        if ($this->serviceAccountFile) {
            $path = $this->serviceAccountFile->storeAs('app', 'firebase-service-account.json', 'local');
            
            Notification::make()
                ->title('Service account file uploaded successfully')
                ->success()
                ->send();
        }

        // Update .env file
        $this->updateEnvFile('FIREBASE_PROJECT_ID', $this->projectId);
        $this->updateEnvFile('FIREBASE_CREDENTIALS', 'storage/app/firebase-service-account.json');

        // Clear config cache
        \Artisan::call('config:clear');
        \Artisan::call('config:cache');

        Notification::make()
            ->title('Firebase settings saved successfully')
            ->success()
            ->send();

        $this->serviceAccountFile = null;
    }

    protected function updateEnvFile(string $key, string $value): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            $envContent .= "\n{$replacement}";
        }

        file_put_contents($envFile, $envContent);
    }

    public function testConnection(): void
    {
        try {
            $credentialsPath = storage_path(config('services.firebase.credentials'));
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Service account file not found');
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            
            if (!isset($credentials['project_id']) || !isset($credentials['private_key'])) {
                throw new \Exception('Invalid service account file');
            }

            Notification::make()
                ->title('Firebase connection successful')
                ->body("Connected to project: {$credentials['project_id']}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Firebase connection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
