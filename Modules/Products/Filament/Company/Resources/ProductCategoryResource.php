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
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;
use Modules\Products\Models\ProductCategory;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return trans('ip.family');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.product_families');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.product_families');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('category_name')
                                    ->label(trans('ip.family'))
                                    ->inlineLabel()
                                    ->autofocus()
                                    ->required(),
                            ]),
                Group::make()->schema([
                    Placeholder::make('explanation Product Family')
                        ->label('just some text'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_name')->label(trans('ip.family')),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('category_name', 'asc');
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
            'index' => Pages\ListProductCategories::route('/'),
        ];
    }
}
