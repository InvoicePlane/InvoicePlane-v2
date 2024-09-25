<?php

namespace Modules\Invoices\Filament\Resources;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Filament\Resources\InvoiceResource\Pages;
use Modules\Invoices\Filament\Resources\InvoiceResource\RelationManagers;
use Modules\Invoices\Models\Invoice;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    Select::make('user_id')
                        ->required()
                        ->relationship('user', 'user_language')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('client_id')
                        ->required()
                        ->relationship('client', 'client_name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('invoice_group_id')
                        ->required()
                        ->relationship('invoiceGroup', 'invoice_group_name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    TextInput::make('invoice_status_id')
                        ->required()
                        ->numeric()
                        ->step(1),

                    Checkbox::make('is_read_only')
                        ->rules(['boolean'])
                        ->nullable()
                        ->inline(),

                    TextInput::make('invoice_password')
                        ->nullable()
                        ->string(),

                    DatePicker::make('invoice_date_created')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    TimePicker::make('invoice_time_created')
                        ->required()
                        ->native(false),

                    DateTimePicker::make('invoice_date_modified')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    DatePicker::make('invoice_date_due')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    TextInput::make('invoice_number')
                        ->nullable()
                        ->string(),

                    TextInput::make('invoice_discount_amount')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    TextInput::make('invoice_discount_percent')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    MarkdownEditor::make('invoice_terms')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                        ])
                        ->columnSpan(2),

                    TextInput::make('invoice_url_key')
                        ->required()
                        ->string()
                        ->unique(
                            'invoices',
                            'invoice_url_key',
                            ignoreRecord: true
                        ),

                    TextInput::make('payment_method')
                        ->required()
                        ->numeric()
                        ->step(1),

                    TextInput::make('creditinvoice_parent_id')
                        ->nullable()
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
                TextColumn::make('invoice_status_id'),

                TextColumn::make('invoiceGroup.invoice_group_name')->hiddenFrom('md'),

                TextColumn::make('invoice_number'),

                TextColumn::make('invoice_date_due')->since(),

                TextColumn::make('client.client_name'),

                TextColumn::make('invoice.invoiceAmount.invoice_total'),
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
            ->defaultSort('invoice_date_due', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClientRelationManager::class,
            RelationManagers\ExpenseRelationManager::class,
            RelationManagers\InvoiceGroupRelationManager::class,
            RelationManagers\InvoiceItemsRelationManager::class,
            RelationManagers\QuoteRelationManager::class,
            RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
