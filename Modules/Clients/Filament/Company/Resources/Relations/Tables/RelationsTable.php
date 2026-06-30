<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\CustomerService;
use Modules\Core\Enums\Permission;
use Modules\Core\Helpers\EnumHelper;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;

class RelationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('primaryContact.fullName')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('relation_type')
                    ->label(trans('ip.type'))
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationType::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(RelationType::class, $state)?->color() ?? 'secondary')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('relation_status')
                    ->label(trans('ip.status'))
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationStatus::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(RelationStatus::class, $state)?->color() ?? 'secondary')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('relation_number')
                    ->label(trans('ip.relation_number'))
                    ->limit(30)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('md'),

                TextColumn::make('company_name')
                    ->label(trans('ip.company_name'))
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('unique_name')
                    ->label(trans('ip.unique_name'))
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),

                TextColumn::make('coc_number')
                    ->label(trans('ip.coc_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('vat_number')
                    ->label(trans('ip.vat_id_short'))
                    ->hiddenFrom('sm')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('language')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make('view'),
                    Action::make('create_invoice')
                        ->label(trans('ip.create_invoice'))
                        ->icon('heroicon-o-document-plus')
                        ->url(fn (Relation $record): string => InvoiceResource::getUrl('create', [
                            'customer_id' => $record->id,
                        ])),
                    Action::make('create_quote')
                        ->label(trans('ip.create_quote'))
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Relation $record): string => QuoteResource::getUrl('create', [
                            'customer_id' => $record->id,
                        ])),
                    EditAction::make('edit')
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_RELATIONS->value))
                        ->action(function (Relation $record, array $data) {
                            app(CustomerService::class)->updateCustomer($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_RELATIONS->value))
                        ->action(function (Relation $record, array $data) {
                            app(\Modules\Clients\Services\RelationService::class)->deleteRelation($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_RELATIONS->value)),
                ]),
            ])->defaultSort('company_name', 'asc');
    }
}
