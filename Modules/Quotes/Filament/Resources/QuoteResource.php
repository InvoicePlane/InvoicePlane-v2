<?php

namespace Modules\Quotes\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
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
use Modules\Quotes\Filament\Resources\QuoteResource\Pages;
use Modules\Quotes\Filament\Resources\QuoteResource\RelationManagers;
use Modules\Quotes\Models\Quote;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrowtrending';

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return trans('crud.quotes.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.quotes.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.quotes.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    Select::make('invoice_id')
                        ->required()
                        ->relationship('invoice', 'invoice_number')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    /*Select::make('user_id')
                        ->required()
                        ->relationship('user', 'user_language')
                        ->searchable()
                        ->preload()
                        ->native(false),*/

                    Select::make('invoice_group_id')
                        ->required()
                        ->relationship('invoiceGroup', 'invoice_group_name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    TextInput::make('quote_status_id')
                        ->required()
                        ->numeric()
                        ->step(1),

                    DatePicker::make('quote_date_created')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    DateTimePicker::make('quote_date_modified')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    DatePicker::make('quote_date_expires')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    TextInput::make('quote_number')
                        ->nullable()
                        ->string(),

                    TextInput::make('quote_discount_amount')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    TextInput::make('quote_discount_percent')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    MarkdownEditor::make('notes')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                        ])
                        ->columnSpan(2),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('quote_status_id'),

                TextColumn::make('invoiceGroup.invoice_group_name'),

                TextColumn::make('quote_date_expires')->since(),

                TextColumn::make('quote_number'),
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
            ->defaultSort('quote_date_expires', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceGroupRelationManager::class,
            RelationManagers\InvoiceRelationManager::class,
            RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuotes::route('/'),
        ];
    }
}
