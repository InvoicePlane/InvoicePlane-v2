<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\CommunicationType;

class CommunicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'communications';

    protected static ?string $title = 'Communication';

    protected static ?string $recordTitleAttribute = 'contactable_value';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('contactable_type')
                            ->label(trans('ip.contact_type'))
                            ->options(
                                collect(CommunicationType::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => trans($case->label())])
                            )
                            ->required(),

                        TextInput::make('contactable_value')
                            ->label(trans('ip.contact_value'))
                            ->required(),
                    ]),

                Toggle::make('is_primary')
                    ->label(trans('ip.primary'))
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contactable_type')->label(trans('ip.contact_type'))->sortable()->searchable(),
                TextColumn::make('contactable_value')->label(trans('ip.contact_value'))->sortable()->searchable(),
                IconColumn::make('is_primary')->boolean()->label(trans('ip.primary')),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('7xl'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
