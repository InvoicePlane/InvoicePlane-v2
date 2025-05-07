<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\TaxRate;

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.tax_rates');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: Code, Name, Active
                        //
                        Section::make(trans('ip.basic_information'))
                            ->schema([
                                // 2-column grid for Code + Active
                                Grid::make(2)
                                    ->columns(2)
                                    // <-- Center *every* grid cell vertically
                                    ->extraAttributes([
                                        'class' => '!items-center',
                                    ])
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.tax_rate_code'))
                                            ->nullable(),

                                        Toggle::make('is_active')
                                            ->label(trans('ip.is_active'))
                                            ->default(true)
                                            ->columnSpan(1)
                                            // no need for h-full, the grid will handle centering
                                            ->extraAttributes([
                                                'class' => '!flex items-center',
                                            ]),
                                    ]),

                                // Name below, full width of the section
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->autofocus(),
                            ])
                            ->columnSpan(1),

                        //
                        // RIGHT COLUMN: Type & Percentage
                        //
                        Section::make(trans('ip.details'))
                            ->schema([
                                Grid::make(2)
                                    ->columns(2)
                                    ->schema([
                                        Select::make('tax_rate_type')
                                            ->label(trans('ip.tax_rate_type'))
                                            ->options(
                                                collect(TaxRateType::cases())
                                                    ->mapWithKeys(fn (TaxRateType $type) => [
                                                        $type->value => trans($type->label()),
                                                    ])
                                                    ->toArray()
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('rate')
                                            ->label(trans('ip.percentage'))
                                            ->required()
                                            ->numeric()
                                            ->step(0.01),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tax_rate_type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(TaxRateType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('name')->limit(10)->label(trans('ip.name'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('rate')->label(trans('ip.percentage'))->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => TaxRateResource\Pages\ListTaxRates::route('/'),
        ];
    }
}
