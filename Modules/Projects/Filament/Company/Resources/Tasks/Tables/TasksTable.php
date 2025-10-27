<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_status')
                    ->label(trans('ip.task_status'))
                    ->badge()
                    ->formatStateUsing(
                        fn (Task $record): string => static::getStatusLabel($record->task_status)
                    )
                    ->sortable()
                    ->searchable()
                    ->color(function (Task $record) {
                        $status = $record->task_status instanceof TaskStatus ? $record->task_status : TaskStatus::tryFrom($record->task_status);

                        return $status?->color() ?? 'secondary';
                    })
                    ->sortable(false),

                TextColumn::make('task_name')
                    ->limit(30)
                    ->label(trans('ip.task_name'))
                    ->tooltip(fn (Task $record) => $record->task_name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label(trans('ip.task_finish_date'))
                    ->since()
                    ->tooltip(fn (Task $record) => $record->due_at?->format('Y-m-d H:i:s'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn (Task $record): ?string => $record->due_at?->isPast() && $record->task_status !== TaskStatus::COMPLETED->value
                            ? 'danger'
                            : null
                    ),

                TextColumn::make('task_price')
                    ->label(trans('ip.task_price'))
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('project.project_name')
                    ->limit(20)
                    ->label(trans('ip.project_name'))
                    ->tooltip(fn (Task $record) => $record->project?->project_name)
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),

                TextColumn::make('project.customer.company_name')
                    ->limit(20)
                    ->label(trans('ip.company_name'))
                    ->tooltip(fn (Task $record) => $record->project?->customer?->company_name)
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->action(
                            fn (Task $record, array $data) => app(TaskService::class)
                                ->updateTask($record, $data)
                        )
                        ->modalWidth('full')
                        ->tooltip(trans('filament-actions::edit.single.label')),
                    DeleteAction::make('delete')
                        ->action(
                            fn (Task $record, array $data) => app(TaskService::class)
                                ->deleteTask($record)
                        ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getStatusLabel(mixed $status): string
    {
        $status = $status instanceof TaskStatus
            ? $status
            : TaskStatus::tryFrom($status);

        return $status?->label() ?? trans('ip.tasks.unknown');
    }
}
