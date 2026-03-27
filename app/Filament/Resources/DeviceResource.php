<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Services\DeviceCommandService;
use App\Services\QrProvisioningService;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationGroup = 'Device Management';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('imei')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('brand')->sortable(),
                Tables\Columns\TextColumn::make('model'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'locked',
                        'warning' => 'kiosk',
                        'gray'    => 'wiped',
                    ]),
                Tables\Columns\IconColumn::make('is_online')->boolean()->label('Online'),
                Tables\Columns\TextColumn::make('last_seen_at')->since()->label('Last Seen'),
                Tables\Columns\TextColumn::make('emiPlan.next_due_date')->date()->label('Next Due'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'locked' => 'Locked', 'kiosk' => 'Kiosk', 'wiped' => 'Wiped']),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn($query) => $query->whereHas('emiPlan', fn($q) =>
                        $q->where('status', 'active')->whereDate('next_due_date', '<', now())
                    ))->label('Overdue EMI'),
                Tables\Filters\Filter::make('online')
                    ->query(fn($query) => $query->where('is_online', true)),
            ])
            ->actions([
                Action::make('lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Device $record) {
                        app(DeviceCommandService::class)->sendCommand($record, 'lock', ['reason' => 'admin_manual']);
                        $record->update(['status' => 'locked', 'lock_reason' => 'admin_manual']);
                        Notification::make()->title('Device locked')->success()->send();
                    }),

                Action::make('unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Device $record) {
                        app(DeviceCommandService::class)->sendCommand($record, 'unlock', ['reason' => 'admin_manual']);
                        $record->update(['status' => 'active', 'lock_reason' => null]);
                        Notification::make()->title('Device unlocked')->success()->send();
                    }),

                Action::make('wipe')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This will factory reset the device. This cannot be undone.')
                    ->action(function (Device $record) {
                        app(DeviceCommandService::class)->sendCommand($record, 'wipe', []);
                        $record->update(['status' => 'wiped']);
                        Notification::make()->title('Wipe command sent')->warning()->send();
                    }),

                Tables\Actions\ViewAction::make(),

                Action::make('generate_qr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn(Device $record) => route('admin.device.qr', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_lock')
                        ->label('Lock Selected')
                        ->icon('heroicon-o-lock-closed')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $svc = app(DeviceCommandService::class);
                            foreach ($records as $device) {
                                $svc->sendCommand($device, 'lock', ['reason' => 'admin_bulk']);
                                $device->update(['status' => 'locked', 'lock_reason' => 'admin_bulk']);
                            }
                            Notification::make()->title('Devices locked')->success()->send();
                        }),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // read-only resource — managed via API
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'view'   => Pages\ViewDevice::route('/{record}'),
        ];
    }
}
