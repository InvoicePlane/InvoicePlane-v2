<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: name, status, project + summary
                        //
                        Schemas\Components\Group::make()
                            ->columnSpan(3)
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
                        Schemas\Components\Group::make()
                            ->columnSpan(2)
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
                                            ->getOptionLabelUsing(fn (string $value) => TaskStatus::tryFrom($value)?->label())
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
}
