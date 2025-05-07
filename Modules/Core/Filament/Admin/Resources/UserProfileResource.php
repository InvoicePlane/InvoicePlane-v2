<?php

namespace Modules\Core\Filament\Admin\Resources;

use Modules\Core\Filament\Admin\Resources\UserProfileResource;

use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\ListUserProfiles;

use Modules\Core\Models\UserProfile;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Models\UserProfile;

class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->required(),
                Forms\Components\TextInput::make('user_phone'),
                Forms\Components\TextInput::make('user_mobile'),
                Forms\Components\TextInput::make('user_language'),
                Forms\Components\TextInput::make('user_web'),
                Forms\Components\TextInput::make('user_vat_id'),
                Forms\Components\TextInput::make('user_tax_code'),
                Forms\Components\TextInput::make('user_iban'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_phone')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_mobile')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_language')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_web')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_vat_id')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_tax_code')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('user_iban')->searchable()->sortable()->toggleable(),
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
     * - user (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => UserProfileResource\Pages\ListUserProfiles::route('/'),
        ];
    }
}
