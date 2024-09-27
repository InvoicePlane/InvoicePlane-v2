<?php

namespace Modules\Core\Filament\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Filament\Resources\TaxRateResource\Pages;
use Modules\Core\Models\TaxRate;

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(heading:null)->schema([
                Grid::make(['default' => 2])->schema([
                    TextInput::make('tax_rate_name')
                        ->nullable()
                        ->string()
                        ->autofocus(),

                    TextInput::make('tax_rate_percent')
                        ->required()
                        ->numeric()
                        ->step(1),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('tax_rate_name'),

                TextColumn::make('tax_rate_percent'),
            ])
            ->filters([])
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
            ->defaultSort('tax_rate_id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTaxRates::route('/'),
        ];
    }
}
