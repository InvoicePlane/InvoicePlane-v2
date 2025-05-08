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
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\ListCustomFieldValues;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldValueResource extends Resource
{
    protected static ?string $model = CustomFieldValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('custom_field_id')->relationship('customField', 'name')->required(),
                TextInput::make('fieldable_type'),
                TextInput::make('fieldable_id'),
                TextInput::make('custom_field_value'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customField.name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('fieldable_type')->searchable()->sortable()->toggleable(),
                TextColumn::make('fieldable_id')->hiddenFrom('sm')->searchable()->sortable()->toggleable(false),
                TextColumn::make('custom_field_value')->limit(10)->searchable()->sortable()->toggleable(),
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
            'index' => ListCustomFieldValues::route('/'),
        ];
    }
}
