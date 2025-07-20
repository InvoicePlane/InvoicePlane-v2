<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
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
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(TaskService::class)->createTask($data);
                })->modalWidth('full'),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return Task::query()
            ->orderByRaw("
                FIELD(task_status,
                    'not_started',
                    'open',
                    'in_progress',
                    'completed',
                    'paid',
                    'cancelled'
                )
            ")
            ->orderBy('due_at', 'asc');
    }
}
