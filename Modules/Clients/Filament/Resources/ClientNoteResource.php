<?php

namespace Modules\Clients\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Clients\Filament\Resources\ClientNoteResource\Pages;
use Modules\Clients\Models\ClientNote;

class ClientNoteResource extends Resource
{
    protected static ?string $model = ClientNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return trans('crud.client_notes.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.client_notes.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.client_notes.collectionTitle');
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageClientNotes::route('/'),
        ];
    }
}
