<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Enum;
use Modules\Projects\Enums\ProjectStatus;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('project_number')
                                            ->label(trans('ip.project_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        TextInput::make('project_name')
                                            ->label(trans('ip.project_name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Select::make('project_status')
                                            ->label(trans('ip.project_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                            ->options(ProjectStatus::options())
                                            ->required()
                                            ->native(false)
                                            ->rule(new Enum(ProjectStatus::class))
                                            ->columnSpan(2),

                                        DatePicker::make('start_at')
                                            ->label(trans('ip.start_at'))
                                            ->prefixIcon(Heroicon::OutlinedCalendar)
                                            ->required()
                                            ->native(false)
                                            ->columnSpan(1),

                                        DatePicker::make('end_at')
                                            ->label(trans('ip.end_at'))
                                            ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                            ->native(false)
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        Section::make(trans('ip.client'))
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('customer_id')
                                    ->label(trans('ip.client'))
                                    ->prefixIcon(Heroicon::OutlinedUserGroup)
                                    ->relationship('customer', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label(trans('ip.customer_name'))
                                            ->required(),
                                    ]),
                            ]),
                    ]),

                Section::make(trans('ip.description'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->collapsed()
                    ->schema([
                        TextInput::make('description')
                            ->label(trans('ip.description'))
                            ->maxLength(65535),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
