<?php

namespace Modules\Projects\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Projects\Enums\TaskStatus;

class RecentTasksWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Tasks';

    protected static ?int $sort = 4;

    /** @phpstan-ignore-next-line */
    protected function getTableQuery(): Builder|Relation|null
    {
        return \Modules\Projects\Models\Task::query()->latest()->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('task_status')
                ->label(trans('ip.task_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => TaskStatus::tryFrom($state)?->label() ?? '-')
                ->color(fn ($state) => TaskStatus::tryFrom($state)?->color() ?? 'secondary'),
            TextColumn::make('task_name')->label(trans('ip.task_name')),
            TextColumn::make('due_at')->label(trans('ip.due_date'))->date(),
        ];
    }
}
