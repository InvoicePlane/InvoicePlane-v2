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
use Modules\Core\Enums\CustomFieldType;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\ListCustomFields;
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
                TextInput::make('fieldable_type'),
                Select::make('type')
                    ->options(
                        collect(CustomFieldType::cases())
                            ->mapWithKeys(fn (CustomFieldType $status) => [
                                $status->value => trans($status->label()),
                            ])
                            ->toArray()
                    )
                    ->required(),
                TextInput::make('field_label'),
                TextInput::make('field_order'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fieldable_type')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(CustomFieldType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('field_label')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('field_order')->searchable()->sortable()->toggleable(),
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
            'index' => ListCustomFields::route('/'),
        ];
    }
}
