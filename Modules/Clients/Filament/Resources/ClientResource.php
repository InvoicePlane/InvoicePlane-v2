<?php

namespace Modules\Clients\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
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
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    DateTimePicker::make('client_date_created')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    DateTimePicker::make('client_date_modified')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    TextInput::make('client_name')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_address_1')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_address_2')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_city')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_state')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_zip')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_country')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_phone')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_fax')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_mobile')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_email')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_web')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_vat_id')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_tax_code')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_language')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_active')
                        ->required()
                        ->numeric()
                        ->step(1),

                    TextInput::make('client_surname')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_avs')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_insurednumber')
                        ->nullable()
                        ->string(),

                    TextInput::make('client_veka')
                        ->nullable()
                        ->string(),

                    DatePicker::make('client_birthdate')
                        ->rules(['date'])
                        ->nullable()
                        ->native(false),

                    TextInput::make('client_gender')
                        ->nullable()
                        ->numeric()
                        ->step(1),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\IconColumn::make('client_active')
                    ->boolean(),

                TextColumn::make('client_name'),

                TextColumn::make('client_phone'),

                TextColumn::make('client_mobile'),

                TextColumn::make('client_email'),

                TextColumn::make('client_vat_id')->hiddenFrom('md'),

                TextColumn::make('client_tax_code')->hiddenFrom('md'),

                TextColumn::make('client_language')->hiddenFrom('md'),
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
}
