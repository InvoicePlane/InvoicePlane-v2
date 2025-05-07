<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\RelationManagers;

use Modules\Core\Enums\CommunicationType;

use Modules\Clients\Filament\Company\Resources\ContactResource\RelationManagers\CommunicationsRelationManager;

use Modules\Core\Models\Communication;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\ContactResource;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class CommunicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'communications';

    protected static ?string $title = 'Communication';

    protected static ?string $recordTitleAttribute = 'contactable_value';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('contactable_type')
                            ->label(trans('ip.contact_type'))
                            ->options(
                                collect(CommunicationType::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => trans($case->label())])
                            )
                            ->required(),

                        Forms\Components\TextInput::make('contactable_value')
                            ->label(trans('ip.contact_value'))
                            ->required(),
                    ]),

                Forms\Components\Toggle::make('is_primary')
                    ->label(trans('ip.primary'))
                    ->inline(false),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contactable_type')->label(trans('ip.contact_type'))->sortable()->searchable(),
                Tables\Columns\TextColumn::make('contactable_value')->label(trans('ip.contact_value'))->sortable()->searchable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean()->label(trans('ip.primary')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalWidth('7xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
