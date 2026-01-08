<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Task;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_status')
                    ->label(trans('ip.task_status'))
                    ->badge()
                    ->formatStateUsing(function (Task $record) {
                        $status = $record->task_status instanceof TaskStatus ? $record->task_status : TaskStatus::tryFrom($record->task_status);

                        return $status?->label() ?? trans('ip.tasks.unknown');
                    })
                    ->color(function (Task $record) {
                        $status = $record->task_status instanceof TaskStatus ? $record->task_status : TaskStatus::tryFrom($record->task_status);

                        return $status?->color() ?? 'secondary';
                    })
                    ->sortable(false),
                TextColumn::make('task_name')
                    ->limit(10)
                    ->label(trans('ip.task_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('due_at')
                    ->label(trans('ip.task_finish_date'))
                    ->since()
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn (Task $record) => $record->due_at
                    && Carbon::parse($record->due_at)->isPast()
                    && $record->task_status !== TaskStatus::COMPLETED->value
                        ? 'danger'
                        : null
                    ),
                TextColumn::make('task_price')
                    ->label(trans('ip.task_price'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.project_name')
                    ->limit(10)
                    ->label(trans('ip.project_name'))
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),
                TextColumn::make('project.customer.company_name')
                    ->limit(10)
                    ->label(trans('ip.company_name'))
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
