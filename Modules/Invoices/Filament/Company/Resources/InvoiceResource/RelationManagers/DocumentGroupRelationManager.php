<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'documentGroup';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('document_group_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_group_name')
            ->columns([
                TextColumn::make('document_group_name'),
            ])
            ->filters([
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('7xl'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                    DeleteAction::make(),
                ]),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
