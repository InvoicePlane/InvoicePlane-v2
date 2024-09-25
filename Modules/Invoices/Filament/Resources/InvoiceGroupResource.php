<?php

namespace Modules\Invoices\Filament\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupResource extends Resource
{
    protected static ?string $model = InvoiceGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectanglegroup';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.invoice_groups');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.invoice_groups');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.invoice_groups');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    TextInput::make('invoice_group_name')
                        ->required()
                        ->string()
                        ->autofocus(),

                    TextInput::make('invoice_group_identifier_format')
                        ->required()
                        ->string(),

                    TextInput::make('invoice_group_next_id')
                        ->required()
                        ->numeric()
                        ->step(1),

                    TextInput::make('invoice_group_left_pad')
                        ->required()
                        ->numeric()
                        ->step(1),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('invoice_group_name'),

                TextColumn::make('invoice_group_identifier_format'),

                TextColumn::make('invoice_group_next_id'),

                TextColumn::make('invoice_group_left_pad'),
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
            ->defaultSort('invoice_group_id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoiceGroups::route('/'),
        ];
    }
}
