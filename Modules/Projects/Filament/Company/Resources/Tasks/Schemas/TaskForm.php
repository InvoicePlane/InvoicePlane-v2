<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.task'))
                            ->icon(Heroicon::OutlinedClipboardDocumentList)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('task_number')
                                            ->label(trans('ip.task_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        TextInput::make('task_name')
                                            ->label(trans('ip.task_name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->columnSpan(2),

                                        Select::make('task_status')
                                            ->label(trans('ip.task_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                            ->options(TaskStatus::options())
                                            ->getOptionLabelUsing(fn ($value) => TaskStatus::tryFrom($value)?->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),

                                        DatePicker::make('due_at')
                                            ->label(trans('ip.task_finish_date'))
                                            ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                            ->date()
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('task_price')
                                            ->label(trans('ip.task_price'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->columnSpan(2),

                                        Select::make('tax_rate_id')
                                            ->label(trans('ip.tax_rate'))
                                            ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                            ->relationship('taxRate', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.project'))
                            ->icon(Heroicon::OutlinedFolderOpen)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('project_id')
                                    ->label(trans('ip.project'))
                                    ->prefixIcon(Heroicon::OutlinedFolderOpen)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->getOptionLabelUsing(fn ($value) => Project::find($value)?->project_name)
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return Project::query()
                                            ->where('project_name', 'like', "%{$search}%")
                                            ->with('customer')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn (Project $p) => [
                                                $p->id => $p->project_name,
                                            ])->toArray();
                                    })
                                    ->createOptionForm([
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                        TextInput::make('project_name')
                                            ->label(trans('ip.project_name'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                            ]),
                    ]),

                Section::make(trans('ip.description'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label(trans('ip.description'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
