<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                // Top: two-column split
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // ── LEFT: just the Name
                                Schemas\Components\Group::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.name'))
                                            ->required(),
                                        TextInput::make('group_identifier_format')
                                            ->label(trans('ip.group_identifier_format'))
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                // ── RIGHT: Next ID / Left Pad / Template Tags
                                Grid::make()
                                    ->schema([
                                        TextInput::make('next_id')
                                            ->label(trans('ip.next_id'))
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('left_pad')
                                            ->label(trans('ip.left_pad'))
                                            ->numeric(),

                                        TextInput::make('last_month')
                                            ->label('Last Month')
                                            ->numeric(),

                                        TextInput::make('last_week')
                                            ->label('Last Week')
                                            ->numeric(),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),  // Ensures the entire section spans full width

                //
                // Below: formatting + tag-picker + helper
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->columns(2)
                            ->schema([
                                Schemas\Components\Group::make()->schema([
                                    TextInput::make('format')
                                        ->label(trans('ip.identifier_formatting'))
                                        ->placeholder('{{month}}-{{day}}-{{number}}')
                                        ->required(),
                                    Select::make('__tag_to_insert')
                                        ->label(trans('ip.template_tags'))
                                        ->options(DocumentGroup::availableTags())
                                        ->placeholder(trans('ip.select_tag'))
                                        ->dehydrated(false)
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                            $current = $get('format') ?? '';
                                            $set('format', $current . $state);
                                            $set('__tag_to_insert', null);
                                        }),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label('')
                                        ->content(trans('ip.identifier_format_template_tags_instructions'))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),  // Ensures the entire section spans full width
            ]);
    }
}
