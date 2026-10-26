<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(TaskService::class)->createTask($data);
                })->modalWidth('full'),
        ];
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder $query */
        $query = Task::query()
            ->orderByRaw("
                CASE task_status
                    WHEN 'not_started' THEN 1
                    WHEN 'open' THEN 2
                    WHEN 'in_progress' THEN 3
                    WHEN 'completed' THEN 4
                    WHEN 'paid' THEN 5
                    WHEN 'cancelled' THEN 6
                    ELSE 7
                END
            ")
            ->orderBy('due_at', 'asc');

        /* @phpstan-ignore-next-line */
        return $query;
    }
}
