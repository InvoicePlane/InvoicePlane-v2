<?php

namespace Modules\Quotes\Filament\Company\Resources\QuoteResource\RelationManagers;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Models\Company;

use Modules\Quotes\Filament\Company\Resources\QuoteResource\RelationManagers\DocumentGroupRelationManager;

use Modules\Quotes\Filament\Company\Resources\QuoteResource;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class DocumentGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'documentGroup';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('document_group_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_group_name')
            ->columns([
                Tables\Columns\TextColumn::make('document_group_name'),
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
