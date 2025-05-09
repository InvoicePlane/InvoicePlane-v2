<?php

namespace Modules\Payments\Filament\Company\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\ListPaymentMethods;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 10;

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
                Grid::make(1)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                TextInput::make('payment_method_name')
                                    ->inlineLabel()
                                    ->label(trans('ip.payment_method'))
                                    ->required()
                                    ->autofocus(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_method_name')->label(trans('ip.payment_method'))->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_method_name', 'asc');
    }

    /**
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentMethods::route('/'),
        ];
    }
}
