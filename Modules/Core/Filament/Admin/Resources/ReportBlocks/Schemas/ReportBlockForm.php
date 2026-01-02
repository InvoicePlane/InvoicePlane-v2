<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockType;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportDataSource;

class ReportBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('ip.report_block_section_general'))
                ->schema([
                    TextInput::make('name')
                        ->label(trans('ip.report_block_name'))
                        ->required()
                        ->maxLength(255),
                    Select::make('width')
                        ->label(trans('ip.report_block_width'))
                        ->options(ReportBlockWidth::class)
                        ->required(),
                    Select::make('block_type')
                        ->label(trans('ip.report_block_type'))
                        ->options(ReportBlockType::class)
                        ->required(),
                    Select::make('data_source')
                        ->label(trans('ip.report_block_data_source'))
                        ->options(ReportDataSource::class)
                        ->required(),
                    Select::make('default_band')
                        ->label(trans('ip.report_block_default_band'))
                        ->options(ReportBand::class)
                        ->required(),
                    Toggle::make('is_active')
                        ->label(trans('ip.report_block_is_active'))
                        ->default(true),
                ]),
            Section::make(trans('ip.report_block_section_field_configuration'))
                ->schema([
                    ViewField::make('fields_canvas')
                        ->view('core::filament.admin.resources.report-blocks.fields-canvas')
                        ->label(trans('ip.report_block_fields_canvas_label'))
                        ->helperText(trans('ip.report_block_fields_canvas_help')),
                ])
                ->collapsible(),
        ]);
    }
}
