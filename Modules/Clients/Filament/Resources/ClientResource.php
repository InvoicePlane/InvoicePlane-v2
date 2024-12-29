<?php

namespace Modules\Clients\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Clients\Filament\Resources\ClientResource\Pages;
use Modules\Clients\Filament\Resources\ClientResource\RelationManagers;
use Modules\Clients\Models\Client;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return trans('crud.clients.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.clients.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.clients.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make()
                    ->label(trans('ip.personal_information'))
                    ->schema([
                        TextInput::make('client_name')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_surname')
                            ->nullable()
                            ->label(trans('ip.client_surname_optional'))
                            ->string(),
                        TextInput::make('language')
                            ->nullable()
                            ->string(),
                    ]),
                Section::make()
                    ->label(trans('ip.address'))
                    ->schema([
                        Grid::make(['default' => 1])->schema([
                            TextInput::make('client_address_1')
                                ->nullable()
                                ->label(trans('ip.street_address'))
                                ->string(),
                            TextInput::make('client_address_2')
                                ->nullable()
                                ->label(trans('ip.street_address_2'))
                                ->string(),
                            TextInput::make('client_city')
                                ->nullable()
                                ->label(trans('ip.city'))
                                ->string(),
                            TextInput::make('client_state')
                                ->nullable()
                                ->label(trans('ip.state'))
                                ->string(),
                            TextInput::make('client_zip')
                                ->nullable()
                                ->label(trans('ip.zip_code'))
                                ->string(),
                            TextInput::make('client_country')
                                ->nullable()
                                ->label(trans('ip.country'))
                                ->string(),
                        ]),
                    ]),
                Section::make('personal_information')->label(trans('ip.personal_information'))->schema([
                    DatePicker::make('client_birthdate')
                        ->rules(['date'])
                        ->label(trans('ip.client_name'))
                        ->nullable()
                        ->native(false),
                    TextInput::make('client_gender')
                        ->nullable()
                        ->label(trans('ip.client_name'))
                        ->numeric()
                        ->step(1),
                ]),
                Section::make()->schema([
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('client_phone')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_fax')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_mobile')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_email')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_web')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_vat_id')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_tax_code')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_avs')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_insurednumber')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                        TextInput::make('client_veka')
                            ->nullable()
                            ->label(trans('ip.client_name'))
                            ->string(),
                    ]),
                ]),
            ]),
            Group::make()->schema([
                Toggle::make('client_active')
                    ->required(),
                DateTimePicker::make('client_date_created')
                    ->disabled()
                    ->rules(['date'])
                    ->required()
                    ->native(false),
                DateTimePicker::make('client_date_modified')
                    ->disabled()
                    ->rules(['date'])
                    ->required()
                    ->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                IconColumn::make('client_active')
                    ->label(trans('ip.active'))
                    ->boolean(),
                TextColumn::make('client_name')
                    ->label(trans('ip.client_name')),
                TextColumn::make('client_email')
                    ->label(trans('ip.email')),
                TextColumn::make('client_phone')
                    ->label(trans('ip.phone')),
                TextColumn::make('client_mobile')
                    ->label(trans('ip.mobile')),
                TextColumn::make('client_vat_id')->hiddenFrom('md')
                    ->label(trans('ip.vat_id')),
                TextColumn::make('client_tax_code')->hiddenFrom('md')
                    ->label(trans('ip.tax_code')),
                TextColumn::make('client_language')->hiddenFrom('md')
                    ->label(trans('ip.language')),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('client_id', 'desc');
    }

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
            'index' => Pages\ManageClients::route('/'),
        ];
    }

    public static function getRoutes(): array
    {
        return [
            'index'  => Pages\ManageClients::route('/'),
            'create' => fn () => route('clients.store'),
            'edit'   => fn ($record) => route('clients.update', $record),
        ];
    }
}
