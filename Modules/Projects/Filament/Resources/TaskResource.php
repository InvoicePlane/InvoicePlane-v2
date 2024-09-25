<?php

namespace Modules\Projects\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Projects\Filament\Resources\TaskResource\Pages;
use Modules\Projects\Models\Task;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-viewcolumns';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 60;

    public static function getModelLabel(): string
    {
        return trans('ip.task');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.tasks');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.tasks');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('task_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'project_name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                        Forms\Components\TextInput::make('project_name')
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
                Tables\Columns\TextColumn::make('project.project_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.client.client_name')
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTasks::route('/'),
        ];
    }
}
