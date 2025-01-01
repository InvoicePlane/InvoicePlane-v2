<?php

namespace Modules\Payments\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
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
use Modules\Payments\Filament\Resources\PaymentResource\Pages;
use Modules\Payments\Filament\Resources\PaymentResource\RelationManagers;
use Modules\Payments\Models\Payment;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return trans('ip.payment');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.payments');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.payments');
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
                                    ->required()
                                    ->relationship('invoice', 'invoice_number')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->disabled(fn (?Payment $record) => $record !== null)
                                    ->extraAttributes(
                                        fn (?Payment $record) => $record !== null ? ['class' => 'disabled-select'] : []
                                    ),
                                TextInput::make('payment_amount'),
                            ])->columns(1),
                        Section::make(heading:null)
                            ->schema([
                                MarkdownEditor::make('payment_note')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                    ])
                                    ->columnSpan('full'),
                            ])->columns(1),
                    ]),
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                                DatePicker::make('payment_date'),
                                Select::make('payment_method_id')
                                    ->required()
                                    ->relationship('paymentMethod', 'payment_method_name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date'),
                TextColumn::make('invoice.invoice_number'),
                TextColumn::make('invoice.invoiceGroup.invoice_group_name')->hiddenFrom('sm'),
                TextColumn::make('invoice.invoice_date_due'),
                TextColumn::make('invoice.client.client_name'),
                TextColumn::make('payment_amount'),
                TextColumn::make('paymentMethod.payment_method_name')->hiddenFrom('sm'),
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
            RelationManagers\InvoiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
