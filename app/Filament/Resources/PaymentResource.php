<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'EMI Management';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.imei')->label('IMEI')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('PKR')->sortable(),
                Tables\Columns\TextColumn::make('late_fee')->money('PKR'),
                Tables\Columns\TextColumn::make('total_paid')->money('PKR')->sortable(),
                Tables\Columns\BadgeColumn::make('method')
                    ->colors(['success' => 'cash', 'info' => 'jazzcash', 'warning' => 'easypaisa']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'completed', 'danger' => 'failed', 'warning' => 'partial']),
                Tables\Columns\IconColumn::make('is_partial')->boolean()->label('Partial'),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['completed' => 'Completed', 'failed' => 'Failed', 'partial' => 'Partial']),
                Tables\Filters\SelectFilter::make('method')
                    ->options(['jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa', 'card' => 'Card', 'cash' => 'Cash', 'bank' => 'Bank']),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPayments::route('/')];
    }
}
