<?php

namespace Modules\Projects\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Helpers\EnumHelper;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Projects\Models\Task;

class RecentTasksWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Tasks';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        // TaskResource only registers an 'index' page — editing happens via
        // a modal action on that page's table, not a dedicated edit/view
        // page — so this is the most specific URL a row can link to.
        return parent::table($table)
            ->recordUrl(fn (Task $record): string => TaskResource::getUrl('index'));
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Task> $query */
        $query = Task::query()->latest()->limit(10);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('task_status')
                ->label(trans('ip.task_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(TaskStatus::class, $state)?->label() ?? '-')
                ->color(fn ($state) => EnumHelper::safeEnum(TaskStatus::class, $state)?->color() ?? 'secondary'),
            TextColumn::make('task_name')->label(trans('ip.task_name')),
            TextColumn::make('due_at')->label(trans('ip.due_date'))->date(),
        ];
    }
}
