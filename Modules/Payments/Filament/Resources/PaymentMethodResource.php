<?php

namespace Modules\Payments\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Payments\Filament\Resources\PaymentMethodResource\Pages;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return trans('ip.payment_method');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.payment_methods');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.payment_methods');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('payment_method_name')
                    ->required()
                    ->autofocus(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_method_name'),
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
            ])
            ->defaultSort('payment_method_name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
        ];
    }
}
