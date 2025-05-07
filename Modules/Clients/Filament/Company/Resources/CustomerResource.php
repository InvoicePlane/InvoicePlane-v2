<?php

namespace Modules\Clients\Filament\Company\Resources;

use Modules\Clients\Enums\RelationType;

use Modules\Core\Filament\Admin\Resources\UserResource\RelationManagers\ExpensesRelationManager;

use Modules\Core\Helpers\EnumHelper;

use Modules\Clients\Models\Contact;

use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\ListCustomers;

use Modules\Core\Filament\Admin\Resources\UserResource\RelationManagers\QuotesRelationManager;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Enums\RelationStatus;

use Modules\Core\Models\Company;

use Modules\Core\Filament\Admin\Resources\UserResource;

use Modules\Clients\Filament\Company\Resources\CustomerResource;

use Modules\Core\Filament\Admin\Resources\UserResource\RelationManagers\InvoicesRelationManager;

use Modules\Clients\Models\Relation;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages;
use Modules\Clients\Filament\Company\Resources\CustomerResource\RelationManagers;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Helpers\EnumHelper;

class CustomerResource extends Resource
{
    protected static ?string $model = Relation::class;

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.client');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.clients');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.clients');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: just a placeholder summary of “Client (Type)”
                        //
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->columns(2)
                                            ->schema([
                                                Select::make('relation_status')
                                                    ->label(trans('ip.status'))
                                                    ->options(
                                                        collect(RelationStatus::cases())
                                                            ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('relation_type')
                                                    ->label(trans('ip.type'))
                                                    ->options(
                                                        collect(RelationType::cases())
                                                            ->mapWithKeys(fn ($r) => [$r->value => $r->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                TextInput::make('company_name')
                                                    ->label(trans('ip.company_name'))
                                                    ->required(),

                                                TextInput::make('trading_name')
                                                    ->label(trans('ip.trading_name')),

                                                TextInput::make('relation_number')
                                                    ->label(trans('ip.relation_number'))
                                                    ->required(),

                                                Fieldset::make(trans('ip.client_information'))
                                                    ->extraAttributes([
                                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                                    ])
                                                    ->schema([
                                                        Placeholder::make('customer_info')
                                                            ->label(trans('ip.client'))
                                                            ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-'),
                                                    ]),
                                            ]),
                                    ]),
                            ])

                            ->columnSpan(1),

                        //
                        // RIGHT COLUMN: all the real inputs, in a 2-column grid
                        //
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('id_number')
                                                    ->label(trans('ip.id_number')),

                                                TextInput::make('coc_number')
                                                    ->label(trans('ip.coc_number')),

                                                TextInput::make('vat_number')
                                                    ->label(trans('ip.vat_id')),

                                                DatePicker::make('registered_at')
                                                    ->label(trans('ip.date'))
                                                    ->required(),

                                                Select::make('primary_contact_id')
                                                    ->label(trans('ip.primary_contact'))
                                                    ->options(
                                                        fn (): array => Contact::query()
                                                            ->orderBy('first_name')
                                                            ->orderBy('last_name')
                                                            ->get()
                                                            ->pluck('full_name', 'id')
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('first_name')
                                                            ->label(trans('ip.first'))
                                                            ->required(),
                                                        TextInput::make('last_name')
                                                            ->label(trans('ip.last'))
                                                            ->required(),
                                                    ]),
                                            ]),
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
                TextColumn::make('relation_type')
                    ->label(trans('ip.type'))
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationType::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(RelationType::class, $state)?->color() ?? 'secondary')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('relation_status')
                    ->label(trans('ip.status'))
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(RelationStatus::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(RelationStatus::class, $state)?->color() ?? 'secondary')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('relation_number')
                    ->label(trans('ip.relation_number'))
                    ->limit(30)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('company_name')
                    ->label(trans('ip.company_name'))
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('coc_number')
                    ->label(trans('ip.coc_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('vat_number')
                    ->label(trans('ip.vat_id_short'))
                    ->hiddenFrom('sm')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('company_name', 'asc')
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('7xl'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\ExpensesRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\QuotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
