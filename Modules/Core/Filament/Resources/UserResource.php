<?php

namespace Modules\Core\Filament\Resources;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\UserType;
use Modules\Core\Filament\Resources\UserResource\Pages;
use Modules\Core\Filament\Resources\UserResource\RelationManagers;
use Modules\Core\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return trans('ip.users');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.users');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(heading:null)->schema([
                Grid::make([
                    'default' => 2,
                ])->schema([
                    TextInput::make('user_type')
                        ->required()
                        ->numeric()
                        ->step(1)
                        ->autofocus(),
                    Checkbox::make('user_active')
                        ->rules(['boolean'])
                        ->nullable()
                        ->inline(),
                    DateTimePicker::make('user_date_created')
                        ->rules(['date'])
                        ->required()
                        ->native(false),
                    DateTimePicker::make('user_date_modified')
                        ->rules(['date'])
                        ->required()
                        ->native(false),
                    TextInput::make('user_language')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_name')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_company')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_address_1')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_address_2')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_city')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_state')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_zip')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_country')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_phone')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_fax')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_mobile')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_email')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_password')
                        ->required()
                        ->string(),
                    TextInput::make('user_web')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_vat_id')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_tax_code')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_psalt')
                        ->nullable()
                        ->string(),
                    Checkbox::make('user_all_clients')
                        ->rules(['boolean'])
                        ->required()
                        ->inline(),
                    TextInput::make('user_passwordreset_token')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_subscribernumber')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_iban')
                        ->nullable()
                        ->string(),
                    TextInput::make('user_gln')
                        ->nullable()
                        ->numeric()
                        ->step(1),
                    TextInput::make('user_rcc')
                        ->nullable()
                        ->string(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('user_name'),
                TextColumn::make('user_type')
                    ->formatStateUsing(fn (User $record): string => trans(UserType::from($record->user_type)->getLabel()))
                    ->badge()
                    ->color(fn (User $record) => UserType::from($record->user_type)->getColor()),
                IconColumn::make('user_active')
                    ->boolean()
                    ->color(fn (User $record) => $record->user_active ? 'success' : 'danger')
                    ->icon(fn (User $record) => $record->user_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                TextColumn::make('email'),
                TextColumn::make('user_language')
                    ->formatStateUsing(fn (User $record): string => match (mb_strtolower($record->user_language)) {
                        'system', 'en', 'english' => 'English',
                        default => $record->user_language,
                    }),
                TextColumn::make('user_company'),
                TextColumn::make('user_phone')->hiddenFrom('md'),
                TextColumn::make('user_mobile')->hiddenFrom('md'),
                TextColumn::make('user_all_clients')->badge()->hiddenFrom('md'),
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
            ->defaultSort('user_id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\ExpensesRelationManager::class,
            RelationManagers\QuotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
