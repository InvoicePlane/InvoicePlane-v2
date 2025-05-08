<?php

namespace Modules\Invoices\Filament\Company\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Invoices\Enums\RecurringFrequency;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\ListRecurringInvoices;
use Modules\Invoices\Models\RecurringInvoice;

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
                Select::make('invoice_id')->relationship('invoice', 'name')->required(),
                Select::make('frequency')
                    ->options(
                        collect(RecurringFrequency::cases())
                            ->mapWithKeys(fn (RecurringFrequency $status) => [
                                $status->value => trans($status->label()),
                            ])
                            ->toArray()
                    )
                    ->required(),
                DatePicker::make('recurring_start_at'),
                DatePicker::make('recurring_end_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('frequency')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RecurringFrequency::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recurring_start_at')->date()->since()->searchable()->sortable()->toggleable(),
                TextColumn::make('recurring_end_at')->date()->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                ]),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListRecurringInvoices::route('/'),
        ];
    }
}
