<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Api\AppUpdateController;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class AppRelease extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $title           = 'App Release Manager';
    protected static string $view = 'filament.pages.app-release';

    public $version_name  = null;
    public $version_code  = null;
    public $release_notes = null;
    public $apk = null;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('version_name')->required()->placeholder('1.2.0'),
            TextInput::make('version_code')->numeric()->required()->placeholder('12'),
            Textarea::make('release_notes')->rows(3),
            FileUpload::make('apk')
                ->label('APK File')
                ->acceptedFileTypes(['application/vnd.android.package-archive', 'application/octet-stream'])
                ->maxSize(204800) // 200MB
                ->disk('public')
                ->directory('temp-uploads')
                ->visibility('private')
                ->required(),
        ];
    }

    public function upload(): void
    {
        $data = $this->form->getState();

        // Get the uploaded file from Livewire temporary storage
        $uploadedFile = $data['apk'];
        
        // Store the APK with a consistent name for QR provisioning
        $latestPath = Storage::disk('public')->putFileAs(
            'releases',
            $uploadedFile,
            'noxlock-latest.apk'
        );
        
        // Also store with version name for history
        $versionedPath = Storage::disk('public')->putFileAs(
            'releases',
            $uploadedFile,
            "noxlock-v{$data['version_name']}.apk"
        );

        // Update manifest
        $releases   = $this->loadReleases();
        $releases[] = [
            'version_code'  => $data['version_code'],
            'version_name'  => $data['version_name'],
            'download_url'  => url(Storage::url("releases/noxlock-v{$data['version_name']}.apk")),
            'latest_url'    => url(Storage::url('releases/noxlock-latest.apk')),
            'release_notes' => $data['release_notes'],
            'uploaded_at'   => now()->toISOString(),
        ];
        
        Storage::disk('public')->put('releases/manifest.json', json_encode($releases, JSON_PRETTY_PRINT));

        Notification::make()
            ->title("v{$data['version_name']} uploaded successfully")
            ->body("APK available at: " . url(Storage::url('releases/noxlock-latest.apk')))
            ->success()
            ->send();
            
        $this->form->fill();
    }

    public function getCurrentRelease(): ?array
    {
        $releases = $this->loadReleases();
        if (empty($releases)) return null;
        usort($releases, fn($a, $b) => $b['version_code'] <=> $a['version_code']);
        return $releases[0];
    }

    private function loadReleases(): array
    {
        if (! Storage::disk('public')->exists('releases/manifest.json')) return [];
        return json_decode(Storage::disk('public')->get('releases/manifest.json'), true) ?? [];
    }
}
