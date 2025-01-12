<?php

namespace Modules\Inventory\Filament\Resources;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Inventory\Filament\Resources\ProductInventoryResource\Pages;
use Modules\Inventory\Filament\Resources\ProductInventoryResource\RelationManagers;
use Modules\Inventory\Models\ProductInventory;

class ProductInventoryResource extends Resource
{
    protected static ?string $model = ProductInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return trans('ip.product_inventory');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.inventory');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.inventory');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema([
                            ])->columns(1),
                        Section::make(heading:null)
                            ->schema([
                                    ])
                                    ->columnSpan('full'),
                            ])->columns(1),
                    ]),
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                            ]),
                        Section::make(heading:null)
                            ->schema(components: [
                            ]),
                        Section::make(heading:'Sumex')
                            ->schema(components: []),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
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
            ->defaultSort('inventory_quantity', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductInventories::route('/'),
        ];
    }
}
