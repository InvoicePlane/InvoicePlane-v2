<?php

namespace Modules\Projects\Filament\Company\Resources;

use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;
use Modules\Projects\Filament\Company\Resources\ProjectResource\RelationManagers;
use Modules\Projects\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationGroup = 'Projects';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.project');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.projects');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.projects');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: Client selector + info
                        //
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.client'))
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.client_name'))
                                                    ->required(),
                                            ])
                                            ->reactive(),

                                        Placeholder::make('customer_info')
                                            ->label(trans('ip.client_information'))
                                            ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-'),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: Project details
                        //
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.project_name'))
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('project_status')
                                            ->label(trans('ip.project_status'))
                                            ->options(
                                                collect(ProjectStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(fn (string $value) => ProjectStatus::from($value)->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        DatePicker::make('start_at')
                                            ->label(trans('ip.start_at'))
                                            ->required()
                                            ->native(false),

                                        DatePicker::make('end_at')
                                            ->label(trans('ip.end_at'))
                                            ->native(false),
                                    ]),
                            ]),
                    ]),

                //
                // DESCRIPTION (collapsed)
                //
                Section::make(trans('ip.description'))
                    ->collapsed()
                    ->schema([
                        TextInput::make('description')
                            ->label(trans('ip.description'))
                            ->maxLength(65535),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->limit(10)
                    ->label(trans('ip.project_name'))
                    ->formatStateUsing(fn ($state) => $state)
                    ->extraAttributes([
                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer.company_name')->limit(10)->label(trans('ip.client_name'))
                    ->searchable()
                    ->sortable()->toggleable(),
                TextColumn::make('project_status')
                    ->label(trans('ip.project_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(ProjectStatus::class, $state)?->label() ?? '-')
                    ->color(fn ($state) => EnumHelper::safeEnum(ProjectStatus::class, $state)?->color() ?? 'secondary')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_at')->hiddenFrom('sm')->date()->since()->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('end_at')->date()->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
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
            ->defaultSort('end_at', 'asc');
    }

    /**
     * - company (BelongsTo)
     * - customer (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
        ];
    }
}
