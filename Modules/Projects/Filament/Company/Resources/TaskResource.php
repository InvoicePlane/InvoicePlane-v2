<?php

namespace Modules\Projects\Filament\Company\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

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
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: name, status, project + summary
                        //
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.task'))
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.task_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus(),

                                        Select::make('project_id')
                                            ->label(trans('ip.project'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->getSearchResultsUsing(function (string $search): array {
                                                return Project::query()
                                                    ->with('customer')
                                                    ->where('name', 'like', "%{$search}%")
                                                    ->orWhereHas('customer', fn ($q) => $q->where('company_name', 'like', "%{$search}%"))
                                                    ->limit(50)
                                                    ->get()
                                                    ->mapWithKeys(fn (Project $p) => [
                                                        $p->id => "{$p->name} – {$p->customer?->company_name}",
                                                    ])->toArray();
                                            })
                                            ->getOptionLabelUsing(fn (int $value): string => (
                                                $p = Project::with('customer')->find($value)
                                            ) ? "{$p->name} – {$p->customer?->company_name}" : '')
                                            ->createOptionForm([
                                                Select::make('customer_id')
                                                    ->label(trans('ip.client'))
                                                    ->relationship('customer', 'company_name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('company_name')
                                                            ->label(trans('ip.client_name'))
                                                            ->required()
                                                            ->maxLength(255),
                                                    ]),
                                                TextInput::make('name')
                                                    ->label(trans('ip.project_name'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),

                                        Placeholder::make('project_summary')
                                            ->label(trans('ip.client_information'))
                                            ->content(
                                                fn (Get $get) => optional($get('project'))->name
                                                . ' – '
                                                . optional($get('project.customer'))->company_name
                                            ),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: due date, price, tax & description
                        //
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->columns(2)
                                    ->schema([
                                        Select::make('task_status')
                                            ->label(trans('ip.task_status'))
                                            ->options(
                                                collect(TaskStatus::cases())
                                                    ->mapWithKeys(fn (TaskStatus $s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(fn (string $value) => TaskStatus::from($value)->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        DatePicker::make('due_at')
                                            ->label(trans('ip.task_finish_date'))
                                            ->required(),

                                        TextInput::make('price')
                                            ->label(trans('ip.task_price'))
                                            ->numeric(),

                                        Select::make('tax_rate_id')
                                            ->label(trans('ip.tax_rate'))
                                            ->relationship('taxRate', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),
                            ]),

                        Section::make(trans('ip.task_notes'))
                            ->schema([
                                MarkdownEditor::make('description')
                                    ->label(trans('ip.notes'))
                                    ->toolbarButtons(['bold', 'italic']),
                            ])
                            ->collapsed(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
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
                    }),
                TextColumn::make('name')
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
                TextColumn::make('price')
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
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_at', 'asc');
    }

    /**
     * - customer (BelongsTo)
     * - project (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
        ];
    }
}
