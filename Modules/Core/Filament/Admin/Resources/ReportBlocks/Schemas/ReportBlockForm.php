<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportDataSource;
use Modules\Core\Models\ReportBlock;

class ReportBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('width')
                        ->options(ReportBlockWidth::class)
                        ->required(),
                    Select::make('block_type')
                        ->options(ReportBlock::query()->pluck('block_type', 'block_type')->toArray())
                        ->required(),
                    Select::make('data_source')
                        ->options(ReportDataSource::class)
                        ->required(),
                    Select::make('default_band')
                        ->options(ReportBand::class)
                        ->required(),
                    Toggle::make('is_active')
                        ->default(true),
                ]),
            Section::make('Field Configuration')
                ->schema([
                    ViewField::make('fields_canvas')
                        ->view('core::filament.admin.resources.report-blocks.fields-canvas')
                        ->label('Drag fields to canvas')
                        ->helperText('Drag available fields to the canvas to configure block layout'),
                ])
                ->collapsible(),
        ]);
    }
}
