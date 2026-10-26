<?php

namespace Modules\Clients\Filament\Company\Resources\Relations;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ViewRelation;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\ExpensesRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\InvoicesRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\NotesRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\ProjectsRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\QuotesRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\TasksRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\Schemas\RelationForm;
use Modules\Clients\Filament\Company\Resources\Relations\Tables\RelationsTable;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Core\Helpers\EnumHelper;

class RelationResource extends BaseResource
{
    protected static ?string $model = Relation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function form(Schema $schema): Schema
    {
        return RelationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RelationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('ip.client_information'))
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('company_name')->label(trans('ip.company_name')),
                        TextEntry::make('trading_name')->label(trans('ip.trading_name')),
                        TextEntry::make('relation_number')->label(trans('ip.relation_number')),
                        TextEntry::make('relation_type')
                            ->label(trans('ip.type'))
                            ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationType::class, $state)?->label() ?? '-')
                            ->badge(),
                        TextEntry::make('relation_status')
                            ->label(trans('ip.status'))
                            ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationStatus::class, $state)?->label() ?? '-')
                            ->badge(),
                        TextEntry::make('registered_at')->label(trans('ip.date'))->date(),
                        TextEntry::make('coc_number')->label(trans('ip.coc_number')),
                        TextEntry::make('vat_number')->label(trans('ip.vat_id')),
                        TextEntry::make('currency_code')->label(trans('ip.currency')),
                    ]),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            InvoicesRelationManager::class,
            QuotesRelationManager::class,
            ExpensesRelationManager::class,
            TasksRelationManager::class,
            ProjectsRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRelations::route('/'),
            'view'  => ViewRelation::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_RELATIONS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_RELATIONS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_RELATIONS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_RELATIONS->value) ?? false;
    }
}
