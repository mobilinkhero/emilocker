<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Services\DeviceCommandService;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDevice extends ViewRecord
{
    protected static string $resource = DeviceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Section::make('ADB Testing Setup')
                ->description('Use this to test without QR provisioning')
                ->schema([
                    TextEntry::make('api_key')
                        ->label('Device API Key')
                        ->copyable()
                        ->copyMessage('API key copied!')
                        ->fontFamily('mono')
                        ->color('warning'),

                    TextEntry::make('adb_command')
                        ->label('ADB Inject Command (WiFi — same network)')
                        ->state(fn($record) =>
                            "adb shell am broadcast -a com.noxlock.ACTION_DEBUG_SETUP " .
                            "-e device_api_key \"{$record->api_key}\" " .
                            "-e api_base_url \"http://192.168.18.99:8000\" " .
                            "--receiver-include-background com.noxlock.emi.locker"
                        )
                        ->copyable()
                        ->copyMessage('Command copied!')
                        ->fontFamily('mono')
                        ->color('info'),
                ])
                ->collapsible(),

            Section::make('Device Info')
                ->schema([
                    TextEntry::make('id')->label('Device ID'),
                    TextEntry::make('imei')->label('IMEI')->copyable(),
                    TextEntry::make('brand'),
                    TextEntry::make('model'),
                    TextEntry::make('android_version')->label('Android'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'active' => 'success',
                            'locked' => 'danger',
                            'kiosk'  => 'warning',
                            default  => 'gray',
                        }),
                    TextEntry::make('is_online')
                        ->label('Online')
                        ->badge()
                        ->state(fn($record) => $record->is_online ? 'Online' : 'Offline')
                        ->color(fn($record) => $record->is_online ? 'success' : 'gray'),
                    TextEntry::make('last_seen_at')->label('Last Seen')->since(),
                    TextEntry::make('customer.name')->label('Customer')->default('Unassigned'),
                ])
                ->columns(2),

            Section::make('EMI Status')
                ->schema([
                    TextEntry::make('emiPlan.status')->label('Plan Status')->badge(),
                    TextEntry::make('emiPlan.remaining_amount')->label('Remaining (PKR)')->money('PKR'),
                    TextEntry::make('emiPlan.next_due_date')->label('Next Due')->date(),
                    TextEntry::make('emiPlan.paid_installments')->label('Paid Installments'),
                ])
                ->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lock')
                ->label('Lock Device')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    app(DeviceCommandService::class)->sendCommand(
                        $this->record, 'lock', ['reason' => 'admin_manual']
                    );
                    $this->record->update(['status' => 'locked', 'lock_reason' => 'admin_manual']);
                    Notification::make()->title('Lock command sent')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('unlock')
                ->label('Unlock Device')
                ->icon('heroicon-o-lock-open')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    app(DeviceCommandService::class)->sendCommand(
                        $this->record, 'unlock', ['reason' => 'admin_manual']
                    );
                    $this->record->update(['status' => 'active', 'lock_reason' => null]);
                    Notification::make()->title('Unlock command sent')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('qr')
                ->label('View QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->url(fn() => route('admin.device.qr', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }
}
