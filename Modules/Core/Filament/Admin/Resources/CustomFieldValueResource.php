<?php

namespace Modules\Core\Filament\Admin\Resources;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\ListCustomFieldValues;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

use Modules\Core\Models\CustomFieldValue;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldValueResource extends Resource
{
    protected static ?string $model = CustomFieldValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('custom_field_id')->relationship('customField', 'name')->required(),
                Forms\Components\TextInput::make('fieldable_type'),
                Forms\Components\TextInput::make('fieldable_id'),
                Forms\Components\TextInput::make('custom_field_value'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customField.name')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('fieldable_type')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('fieldable_id')->hiddenFrom('sm')->searchable()->sortable()->toggleable(false),
                Tables\Columns\TextColumn::make('custom_field_value')->limit(10)->searchable()->sortable()->toggleable(),
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
     * - customField (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => CustomFieldValueResource\Pages\ListCustomFieldValues::route('/'),
        ];
    }
}
