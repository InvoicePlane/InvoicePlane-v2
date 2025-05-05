<?php

namespace Modules\Products\Filament\Company\Resources\ItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class QuoteItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'quoteItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_id')
            ->columns([
                Tables\Columns\TextColumn::make('item_id'),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalWidth('7xl'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
