<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\ListDocumentGroups;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupResource extends Resource
{
    protected static ?string $model = DocumentGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                // Top: two-column split
                //

                Section::make()->schema([
                    Grid::make(2)
                        ->schema([
                            // ── LEFT: just the Name
                            Group::make()
                                ->schema([
                                    TextInput::make('document_group_name')
                                        ->label(trans('ip.document_group_name'))
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
                                ])
                                ->columnSpan(1),
                        ]),
                ]),

                //
                // Below: formatting + tag-picker + helper
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->columns(2)
                            ->schema([
                                Group::make()->schema([
                                    TextInput::make('format')
                                        ->label(trans('ip.identifier_formatting'))
                                        ->placeholder('{{month}}-{{day}}-{{number}}')
                                        ->required(),
                                    Select::make('__tag_to_insert')
                                        ->label(trans('ip.template_tags'))
                                        ->options(DocumentGroup::availableTags())
                                        ->placeholder(trans('ip.select_tag'))
                                        ->dehydrated(false)              // ← do not persist this field
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                            // append the chosen tag into your real `format` field
                                            $current = $get('format') ?? '';
                                            $set('format', $current . $state);
                                            // clear the helper select
                                            $set('__tag_to_insert', null);
                                        }),
                                ]),
                                Group::make()->schema([
                                    // helper text under the two inputs
                                    Placeholder::make('format_helper')
                                        ->label('')
                                        ->content(trans('ip.identifier_format_template_tags_instructions'))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->limit(10)
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof DocumentGroupType) {
                            return $state->label();
                        }

                        $status = EnumHelper::safeEnum(DocumentGroupType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        if ($state instanceof DocumentGroupType) {
                            return $state->color();
                        }

                        $status = EnumHelper::safeEnum(DocumentGroupType::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('document_group_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('left_pad')->searchable()->sortable()->toggleable(),
                TextColumn::make('format')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('next_id')->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                ]),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * - company (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentGroups::route('/'),
        ];
    }
}
