<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Enums\CustomFieldType;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\CustomField;

class CustomFieldResource extends Resource
{
    protected static ?string $model = CustomField::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('fieldable_type'),
                Forms\Components\Select::make('type')
                    ->options(
                        collect(CustomFieldType::cases())
                            ->mapWithKeys(fn (CustomFieldType $status) => [
                                $status->value => trans($status->label()),
                            ])
                            ->toArray()
                    )
                    ->required(),
                Forms\Components\TextInput::make('field_label'),
                Forms\Components\TextInput::make('field_order'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fieldable_type')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(CustomFieldType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('field_label')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('field_order')->searchable()->sortable()->toggleable(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => CustomFieldResource\Pages\ListCustomFields::route('/'),
        ];
    }
}
