<?php

namespace Modules\Products\Filament\Resources;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages;
use Modules\Products\Models\ProductFamily;

class ProductFamilyResource extends Resource
{
    protected static ?string $model = ProductFamily::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 50;

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
                Group::make()->schema([
                    TextInput::make('family_name')
                        ->inlineLabel()
                        ->label(trans('ip.family'))
                        ->required()
                        ->autofocus(),
                ]),
                Group::make()->schema([
                    Placeholder::make('explanation Product Family')
                        ->label('Material Type'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('family_name')->label(trans('ip.family')),
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
            ->defaultSort('family_name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductFamilies::route('/'),
        ];
    }
}
