<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\CustomerService;
use Modules\Clients\Services\RelationMergeService;
use Modules\Core\Enums\Permission;
use Modules\Core\Helpers\EnumHelper;

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
                    static::makeMergeBulkAction(),
                ]),
            ])->defaultSort('company_name', 'asc');
    }

    protected static function makeMergeBulkAction(): BulkAction
    {
        return BulkAction::make('merge')
            ->label(trans('ip.merge_clients'))
            ->icon('heroicon-o-arrows-pointing-in')
            ->visible(fn () => auth()->user()?->can(Permission::EDIT_RELATIONS->value))
            ->modalHeading(trans('ip.merge_clients'))
            ->modalDescription(trans('ip.merge_clients_description'))
            ->schema(fn (Collection $records): array => [
                Radio::make('primary_id')
                    ->label(trans('ip.merge_clients_primary'))
                    ->helperText(trans('ip.merge_clients_primary_help'))
                    ->options(
                        $records->mapWithKeys(fn (Relation $record) => [
                            $record->id => "{$record->company_name} ({$record->relation_number})",
                        ])->all()
                    )
                    ->required(),
            ])
            ->action(function (Collection $records, array $data): void {
                if ($records->count() !== 2) {
                    Notification::make()
                        ->title(trans('ip.merge_clients_select_two'))
                        ->danger()
                        ->send();

                    return;
                }

                $primary   = $records->firstWhere('id', (int) $data['primary_id']);
                $duplicate = $records->firstWhere(fn (Relation $record) => $record->id !== (int) $data['primary_id']);

                if ( ! $primary instanceof Relation || ! $duplicate instanceof Relation) {
                    Notification::make()
                        ->title(trans('ip.merge_clients_select_two'))
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    app(RelationMergeService::class)->merge($primary, $duplicate);
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(trans('ip.merge_clients_success'))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
