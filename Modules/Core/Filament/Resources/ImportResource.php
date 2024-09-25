<?php

namespace Modules\Core\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Filament\Resources\ImportResource\Pages;
use Modules\Core\Models\Import;

class ImportResource extends Resource
{
    protected static ?string $model = Import::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmarksquare';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return trans('ip.import');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.import');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.imports.collectionTitle');
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageImports::route('/'),
        ];
    }
}
