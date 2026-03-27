<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([

            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->label('Assign to Customer')
                ->helperText('Optional — can be assigned later.'),

            TextInput::make('api_key')
                ->label('Device API Key')
                ->default(fn() => Str::random(48))
                ->disabled()
                ->dehydrated()
                ->helperText('Auto-generated. Embed this in QR or inject via ADB for testing.'),

            TextInput::make('imei')
                ->label('IMEI')
                ->nullable()
                ->helperText('Optional — filled automatically when device registers.'),

            Placeholder::make('adb_hint')
                ->label('ADB Testing Command')
                ->content(fn($get) =>
                    'After saving, run: php artisan noxlock:test-device' . PHP_EOL .
                    'Or inject manually via ADB broadcast.'
                ),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status']          = config('noxlock.default_device_status', 'active');
        $data['model']           = 'Pending';
        $data['brand']           = 'Pending';
        $data['android_version'] = 'Pending';
        $data['imei']            = $data['imei'] ?? 'PENDING_' . Str::random(8);
        return $data;
    }

    protected function afterCreate(): void
    {
        $apiKey = $this->record->api_key;

        Notification::make()
            ->title('Device slot created')
            ->body("API Key: {$apiKey}")
            ->success()
            ->persistent()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
