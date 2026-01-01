<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\ReportTemplateType;

class ReportTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(trans('ip.template_name'))
                                    ->required()
                                    ->maxLength(255),
                                Select::make('template_type')
                                    ->label(trans('ip.template_type'))
                                    ->required()
                                    ->options(
                                        collect(ReportTemplateType::cases())
                                            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
                                    ),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Checkbox::make('is_active')
                                    ->label(trans('ip.active'))
                                    ->default(true),
                                Checkbox::make('is_system')
                                    ->label(trans('ip.system_template'))
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
