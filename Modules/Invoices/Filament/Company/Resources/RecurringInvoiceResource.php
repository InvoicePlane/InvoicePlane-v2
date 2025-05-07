<?php

namespace Modules\Invoices\Filament\Company\Resources;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\ListRecurringInvoices;

use Modules\Invoices\Models\RecurringInvoice;

use Modules\Core\Helpers\EnumHelper;

use Modules\Invoices\Enums\RecurringFrequency;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

class RecurringInvoiceResource extends Resource
{
    protected static ?string $model = RecurringInvoice::class;

    protected static ?string $navigationGroup = 'Invoices';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.recurring_invoice');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.recurring_invoices');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.recurring_invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')->relationship('invoice', 'name')->required(),
                Forms\Components\Select::make('frequency')
                    ->options(
                        collect(RecurringFrequency::cases())
                            ->mapWithKeys(fn (RecurringFrequency $status) => [
                                $status->value => trans($status->label()),
                            ])
                            ->toArray()
                    )
                    ->required(),
                Forms\Components\DatePicker::make('recurring_start_at'),
                Forms\Components\DatePicker::make('recurring_end_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('frequency')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RecurringFrequency::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('recurring_start_at')->date()->since()->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('recurring_end_at')->date()->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * - invoice (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecurringInvoices::route('/'),
        ];
    }
}
