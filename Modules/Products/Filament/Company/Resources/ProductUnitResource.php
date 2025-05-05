<?php

namespace Modules\Products\Filament\Company\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;
use Modules\Products\Models\ProductUnit;

class ProductUnitResource extends Resource
{
    protected static ?string $model = ProductUnit::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return trans('ip.unit');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.product_units');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.product_units');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('unit_name')
                                ->inlineLabel()
                                ->label(trans('ip.unit'))
                                ->required()
                                ->autofocus(),
                            TextInput::make('unit_name_plrl')
                                ->inlineLabel()
                                ->label(trans('ip.unit_name_plrl'))
                                ->required(),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_name')->label(trans('ip.unit_name')),
                TextColumn::make('unit_name_plrl')->label(trans('ip.unit_name_plrl')),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProductUnits::route('/'),
        ];
    }
}
