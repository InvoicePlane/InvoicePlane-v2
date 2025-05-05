<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages;
use Modules\Core\Filament\Admin\Resources\UserResource\RelationManagers;
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
        return $form
            ->schema([
                // 4‐column grid so we can do a 1:3 split
                Grid::make(2)
                    ->schema([
                        Section::make(trans('ip.personal_information'))
                            ->columnSpan(1)      // ← 1/4 width
                            ->columns(1)         // only one field wide inside
                            ->schema([
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->autofocus()
                                    ->maxLength(255),
                            ]),

                        Section::make()
                            ->columnSpan(1)      // ← 3/4 width
                            ->columns(1)         // two fields side by side inside
                            ->schema([
                                TextInput::make('email')
                                    ->label(trans('ip.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                DatePicker::make('email_verified_at')
                                    ->label(trans('ip.email_verified_at')),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->limit(20)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label(trans('ip.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email_verified_at')
                    ->label(trans('ip.email_verified_at'))
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\ExpensesRelationManager::class,
            RelationManagers\QuotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
