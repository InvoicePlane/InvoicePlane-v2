<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\ExpenseRelationManager;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class ExpenseRelationManager extends RelationManager
{
    protected static string $relationship = 'expense';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('category_id')
            ->columns([
                Tables\Columns\TextColumn::make('category_id'),
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
