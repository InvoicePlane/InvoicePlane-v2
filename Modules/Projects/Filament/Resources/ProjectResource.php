<?php

namespace Modules\Projects\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Projects\Filament\Resources\ProjectResource\Pages;
use Modules\Projects\Filament\Resources\ProjectResource\RelationManagers;
use Modules\Projects\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-tablecells';

    protected static ?int $navigationSort = 60;

    public static function getModelLabel(): string
    {
        return trans('ip.project');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.projects');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.projects');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('client_name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.client_name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('project_name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClientRelationManager::class,
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProjects::route('/'),
        ];
    }
}
