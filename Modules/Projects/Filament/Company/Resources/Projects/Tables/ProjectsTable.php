<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Projects\Enums\ProjectStatus;

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
                TextColumn::make('customer.company_name')->limit(10)->label(trans('ip.client_name'))
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
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('end_at', 'asc');
    }
}
