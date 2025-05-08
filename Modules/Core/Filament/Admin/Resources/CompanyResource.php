<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\ListCompanies;
use Modules\Core\Models\Company;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Section::make(trans('ip.basic'))
                            ->columnSpan(1)
                            ->columns(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->reactive() // so we can watch its changes
                                    ->afterStateUpdated(function (callable $set, $state): void {
                                        // whenever 'name' changes, regenerate 'slug'
                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->label(trans('ip.slug'))
                                    ->disabled() // can't manually edit
                                    ->required()
                                    ->reactive(), // stays in sync
                            ]),

                        //
                        // ─── RIGHT COLUMN (3/4 width) ────────────────────────────────
                        //
                        Section::make(trans('ip.details'))
                            ->columnSpan(1)   // 3/4 of the total width
                            ->columns(2)      // two‐columns inside
                            ->schema([
                                TextInput::make('search_code')
                                    ->label(trans('ip.search_code'))
                                    ->required(),
                                TextInput::make('vat_number')
                                    ->label(trans('ip.vat_id'))
                                    ->nullable(),

                                TextInput::make('id_number')
                                    ->label(trans('ip.id_number'))
                                    ->nullable(),

                                TextInput::make('coc_number')
                                    ->label(trans('ip.coc_number'))
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('search_code')->searchable()->sortable()->toggleable(),
                TextColumn::make('slug')->searchable()->sortable()->toggleable(),
                TextColumn::make('name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('vat_number')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('id_number')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('coc_number')->searchable()->sortable()->toggleable(),
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
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
        ];
    }
}
