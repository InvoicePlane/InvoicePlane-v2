<?php

namespace Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                    ->label('Template Name')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('template_type')
                                    ->label('Template Type')
                                    ->required()
                                    ->options([
                                        'invoice'  => 'Invoice',
                                        'quote'    => 'Quote',
                                        'estimate' => 'Estimate',
                                    ]),
                            ]),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000),
                        Grid::make(2)
                            ->schema([
                                Checkbox::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                Checkbox::make('is_system')
                                    ->label('System Template')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
