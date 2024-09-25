<?php

namespace Modules\Payments\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Payments\Filament\Resources\PaymentResource\Pages;
use Modules\Payments\Filament\Resources\PaymentResource\RelationManagers;
use Modules\Payments\Models\Payment;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-creditcard';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return trans('ip.payment');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.payments');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.payments');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date'),

                TextColumn::make('invoice.invoice_number'),

                TextColumn::make('invoice.invoiceGroup.invoice_group_name')->hiddenFrom('sm'),

                TextColumn::make('invoice.invoice_date_due'),

                TextColumn::make('invoice.client.client_name'),

                TextColumn::make('payment_amount'),

                TextColumn::make('paymentMethod.payment_method_name')->hiddenFrom('sm'),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
