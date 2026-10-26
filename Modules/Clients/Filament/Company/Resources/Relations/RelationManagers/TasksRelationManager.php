<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_number')->sortable()->searchable(),
                TextColumn::make('task_name')->limit(40)->sortable()->searchable(),
                TextColumn::make('task_status')->sortable()->badge(),
                TextColumn::make('due_at')->date()->sortable(),
                TextColumn::make('task_price')->money()->sortable(),
            ]);
    }
}
