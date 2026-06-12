<?php

namespace Modules\Projects\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Helpers\EnumHelper;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

class RecentProjectsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Projects';

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Project> $query */
        $query = Project::query()->latest()->limit(10);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('project_name')->label(trans('ip.project_name')),
            TextColumn::make('customer.company_name')->label(trans('ip.customer_name')),
            TextColumn::make('project_status')
                ->label(trans('ip.project_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => ($enum = EnumHelper::safeEnum(ProjectStatus::class, $state)) && method_exists($enum, 'label') ? $enum->label() : '-')
                ->color(fn ($state) => ($enum = EnumHelper::safeEnum(ProjectStatus::class, $state)) && method_exists($enum, 'color') ? $enum->color() : 'secondary'),
        ];
    }
}
