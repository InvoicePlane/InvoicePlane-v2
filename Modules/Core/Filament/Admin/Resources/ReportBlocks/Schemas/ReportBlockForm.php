<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages\ReportBuilder;

class ReportBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('block_type')
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('filename')
                ->maxLength(255),
            Select::make('width')
                ->options(ReportBlockWidth::class)
                ->required(),
            TextInput::make('data_source')
                ->required()
                ->maxLength(255),
            TextInput::make('default_band')
                ->required()
                ->maxLength(255),
            Toggle::make('is_active')
                ->default(true),
            Toggle::make('is_system')
                ->default(false),
            Repeater::make('config.fields')
                ->schema([
                    Select::make('id')
                        ->options(function () {
                            $fields  = (new ReportBuilder())->getAvailableFields();
                            $options = [];
                            foreach ($fields as $field) {
                                $options[$field['id']] = $field['label'];
                            }

                            return $options;
                        })
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('label', (new ReportBuilder())->getAvailableFields()[array_search($state, array_column((new ReportBuilder())->getAvailableFields(), 'id'))]['label'] ?? '')),
                    TextInput::make('label')
                        ->required(),
                ])
                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                ->reorderable()
                ->collapsible()
                ->grid(2),
        ]);
    }
}
