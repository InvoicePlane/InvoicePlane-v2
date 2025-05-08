<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\ListUserProfiles;
use Modules\Core\Models\UserProfile;

class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')->relationship('user', 'name')->required(),
                TextInput::make('user_phone'),
                TextInput::make('user_mobile'),
                TextInput::make('user_language'),
                TextInput::make('user_web'),
                TextInput::make('user_vat_id'),
                TextInput::make('user_tax_code'),
                TextInput::make('user_iban'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('user_phone')->searchable()->sortable()->toggleable(),
                TextColumn::make('user_mobile')->searchable()->sortable()->toggleable(),
                TextColumn::make('user_language')->searchable()->sortable()->toggleable(),
                TextColumn::make('user_web')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('user_vat_id')->searchable()->sortable()->toggleable(),
                TextColumn::make('user_tax_code')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('user_iban')->searchable()->sortable()->toggleable(),
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
            'index' => ListUserProfiles::route('/'),
        ];
    }
}
