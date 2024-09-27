<?php

namespace Modules\Projects\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Filament\Resources\TaskResource\Pages;
use Modules\Projects\Models\Task;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Group::make()->schema([
                    TextInput::make('task_name')
                        ->required()
                        ->maxLength(255)
                        ->autofocus(),
                    DatePicker::make('task_finish_date')
                        ->label(trans('ip.task_finish_date')),
                    TextInput::make('task_price')
                        ->label(trans('ip.task_price')),
                    MarkdownEditor::make('task_description')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                        ]),
                ]),
                Group::make()->schema([
                    Select::make('task_status')
                        ->label(trans('ip.status'))
                        ->required()
                        ->options(array_map(fn (TaskStatus $status) => trans($status->getLabel()), TaskStatus::cases()))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->getOptionLabelUsing(fn (string $value) => TaskStatus::from($value)->getLabel()),
                    Select::make('project_id')
                        ->relationship('project', 'project_name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Select::make('client_id')
                                ->relationship('client', 'client_name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('client_name')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->required(),
                            TextInput::make('project_name')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->required(),
                    Select::make('tax_rate_id')
                        ->relationship('taxRate', 'tax_rate_name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_status')
                    ->label(trans('ip.status'))
                    ->badge()
                    ->formatStateUsing(fn (Task $record) => trans(TaskStatus::from($record->task_status)->getLabel()))
                    ->color(fn (Task $record) => TaskStatus::from($record->task_status)->getColor()),
                TextColumn::make('task_name')
                    ->label(trans('ip.task_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('task_finish_date')
                    ->label(trans('ip.task_finish_date'))
                    ->since()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('task_price')
                    ->label(trans('ip.task_price'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.project_name')
                    ->label(trans('ip.project_name'))
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),
                TextColumn::make('project.client.client_name')
                    ->label(trans('ip.client_name'))
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),
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
