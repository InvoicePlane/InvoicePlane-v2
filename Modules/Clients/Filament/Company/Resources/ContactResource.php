<?php

namespace Modules\Clients\Filament\Company\Resources;

use Modules\Clients\Enums\RelationType;

use Modules\Core\Helpers\EnumHelper;

use Modules\Core\Enums\CommunicationType;

use Modules\Clients\Filament\Company\Resources\ContactResource\RelationManagers\CommunicationsRelationManager;

use Modules\Clients\Models\Contact;

use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\ListContacts;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Core\Enums\Gender;

use Modules\Core\Filament\Admin\Resources\AbstractTenantResource;

use Modules\Clients\Filament\Company\Resources\ContactResource;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

class ContactResource extends AbstractTenantResource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 15;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.contact');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.contacts');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.contacts');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: choose client + summary
                        //
                        Section::make(trans('ip.client'))
                            ->columnSpan(1)
                            ->schema([
                                Select::make('relation_id')
                                    ->relationship('relation', 'company_name')
                                    ->label(trans('ip.client'))
                                    ->searchable()
                                    ->preload()
                                    ->required(trans('ip.relation_required'))
                                    ->native(false)
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label(trans('ip.client_name'))
                                            ->required(),
                                    ])
                                    ->reactive(),

                                Fieldset::make(trans('ip.client_information'))
                                    ->extraAttributes([
                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                    ])
                                    ->columns(1)
                                    ->schema([
                                        Placeholder::make('relation_info')
                                            ->label(trans('ip.client'))
                                            ->content(fn (Get $get) => optional($get('relation'))->company_name ?? '-'),
                                    ])
                                    ->visible(fn (Get $get) => filled($get('relation_id'))),
                            ]),

                        //
                        // RIGHT COLUMN: personal info + primary contacts
                        //
                        Section::make(trans('ip.personal_information'))
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(trans('ip.first_name'))
                                    ->required(),

                                TextInput::make('last_name')
                                    ->label(trans('ip.last_name'))
                                    ->required(),

                                Placeholder::make('primary_email')
                                    ->label(trans('ip.email'))
                                    ->content(
                                        fn (?Contact $record = null) => $record ?
                                            optional($record->communications)
                                                ->where('contactable_type', CommunicationType::EMAIL->value)
                                                ->where('is_primary', true)
                                                ->first()?->contactable_value ?? '-'
                                            : '-'
                                    ),

                                Placeholder::make('primary_phone')
                                    ->label(trans('ip.phone'))
                                    ->content(
                                        fn (?Contact $record = null) => $record ?
                                            optional($record->communications)
                                                ->where('contactable_type', CommunicationType::PHONE->value)
                                                ->where('is_primary', true)
                                                ->first()?->contactable_value ?? '-'
                                            : '-'
                                    ),

                                Select::make('gender')
                                    ->label(trans('ip.gender'))
                                    ->options(
                                        collect(Gender::cases())
                                            ->mapWithKeys(fn (Gender $g) => [$g->value => trans($g->label())])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('relation.company_name')->limit(10)->label(trans('ip.company_name'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('relation.relation_type')
                    ->limit(10)
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RelationType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        $status = EnumHelper::safeEnum(RelationType::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label(trans('ip.contact_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('primary_email')
                    ->label(trans('ip.email'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('primary_phone')
                    ->label(trans('ip.phone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gender')
                    ->hiddenFrom('sm')
                    ->label(trans('ip.gender'))
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(Gender::class, $state);

                        return $status?->label() ?? '-';
                    }),
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
            ]);
    }

    /**
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
            CommunicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
        ];
    }
}
