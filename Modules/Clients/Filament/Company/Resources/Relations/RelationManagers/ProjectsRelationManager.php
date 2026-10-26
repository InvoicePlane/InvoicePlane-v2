<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project_number')->sortable()->searchable(),
                TextColumn::make('project_name')->limit(40)->sortable()->searchable(),
                TextColumn::make('project_status')->sortable()->badge(),
                TextColumn::make('start_at')->date()->sortable(),
                TextColumn::make('end_at')->date()->sortable(),
            ]);
    }
}
