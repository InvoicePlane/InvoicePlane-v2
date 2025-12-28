<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectService;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project_name')
                    ->limit(10)
                    ->label(trans('ip.project_name'))
                    ->formatStateUsing(fn ($state) => $state)
                    ->extraAttributes([
                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.company_name')->limit(10)->label(trans('ip.customer_name'))
                    ->searchable()
                    ->sortable()->toggleable(),
                TextColumn::make('project_status')
                    ->label(trans('ip.project_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(ProjectStatus::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(ProjectStatus::class, $state)?->color() ?? 'secondary')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_at')->hiddenFrom('sm')->date()->since()->searchable()->sortable()->toggleable(),
                TextColumn::make('end_at')->date()->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Project $record, array $data) {
                            app(ProjectService::class)->updateProject($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Project $record, array $data) {
                            app(ProjectService::class)->deleteProject($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('end_at', 'asc');
    }
}
