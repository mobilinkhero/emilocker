<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class FirebaseSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    
    protected static ?string $navigationLabel = 'Firebase Settings';
    
    protected static ?string $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.firebase-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'project_id' => config('services.firebase.project_id'),
            'credentials_path' => config('services.firebase.credentials'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Firebase Configuration')
                    ->description('Upload your Firebase service account JSON file and configure project settings.')
                    ->schema([
                        TextInput::make('project_id')
                            ->label('Firebase Project ID')
                            ->required()
                            ->placeholder('your-project-id')
                            ->helperText('Enter your Firebase project ID (e.g., emilockersystem)'),

                        FileUpload::make('service_account')
                            ->label('Service Account JSON File')
                            ->acceptedFileTypes(['application/json'])
                            ->disk('local')
                            ->directory('app')
                            ->visibility('private')
                            ->downloadable()
                            ->helperText('Upload your firebase-service-account.json file from Firebase Console')
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    // Rename the uploaded file to firebase-service-account.json
                                    $uploadedPath = $state->store('app', 'local');
                                    $targetPath = 'app/firebase-service-account.json';
                                    
                                    if (Storage::disk('local')->exists($targetPath)) {
                                        Storage::disk('local')->delete($targetPath);
                                    }
                                    
                                    Storage::disk('local')->move($uploadedPath, $targetPath);
                                    
                                    Notification::make()
                                        ->title('Service account file uploaded successfully')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Update .env file
        $this->updateEnvFile('FIREBASE_PROJECT_ID', $data['project_id']);
        $this->updateEnvFile('FIREBASE_CREDENTIALS', 'storage/app/firebase-service-account.json');

        // Clear config cache
        \Artisan::call('config:clear');
        \Artisan::call('config:cache');

        Notification::make()
            ->title('Firebase settings saved successfully')
            ->success()
            ->send();
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
