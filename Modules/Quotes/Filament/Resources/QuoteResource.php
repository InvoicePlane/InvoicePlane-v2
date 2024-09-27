<?php

namespace Modules\Quotes\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Resources\QuoteResource\Pages;
use Modules\Quotes\Filament\Resources\QuoteResource\RelationManagers;
use Modules\Quotes\Models\Quote;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema([
                                Select::make('invoice_id')
                                    ->relationship('invoice', 'invoice_number')
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                            ])->columns(1),
                        Section::make(heading:null)
                            ->schema([
                                MarkdownEditor::make('notes')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                    ]),
                            ])->columns(1),
                    ]),
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                                Grid::make()->schema([
                                    TextInput::make('quote_number'),
                                    Select::make('quote_status_id')
                                        ->label(trans('hello world!'))
                                        ->required()
                                        ->options(array_map(fn (QuoteStatus $status) => trans($status->getLabel()), QuoteStatus::cases()))
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->getOptionLabelUsing(fn (string $value) => QuoteStatus::from($value)->getLabel()),
                                    DatePicker::make('quote_date_expires'),
                                    Select::make('invoice_group_id')
                                        ->required()
                                        ->relationship('invoiceGroup', 'invoice_group_name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function oldForm(Form $form): Form
    {
        return $form->schema([
            Section::make(heading:null)->schema([
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
                TextColumn::make('quote_status_id')
                    ->label(trans('ip.status'))
                    ->badge()
                    ->formatStateUsing(fn (Quote $record) => trans(QuoteStatus::from($record->quote_status_id)->getLabel()))
                    ->color(fn (Quote $record) => QuoteStatus::from($record->quote_status_id)->getColor()),
                TextColumn::make('quote_number')->label(trans('ip.quote')),
                TextColumn::make('invoiceGroup.invoice_group_name')->label(trans('ip.invoice_group')),
                TextColumn::make('client.client_name')->label(trans('ip.client_name')),
                TextColumn::make('quote_date_expires')
                    ->label(trans('ip.expires'))
                    ->color(fn (Quote $record) => Carbon::parse($record->quote_date_expires)->isPast() ? 'text-red-500' : null)
                    ->since(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('download pdf')
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Quote through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Quote $record): void {}),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->modalDescription('todo: make sure we can email the Quote through an action,
                            so need for modal anymore')
                        ->action(function (Quote $record): void {}),
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
