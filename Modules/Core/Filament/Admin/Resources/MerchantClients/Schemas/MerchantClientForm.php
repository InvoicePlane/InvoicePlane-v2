<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Models\Company;

class MerchantClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Credential Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('company_id')
                                    ->label('Company')
                                    ->options(Company::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),

                                TextInput::make('driver')
                                    ->label('Driver')
                                    ->placeholder('e.g., lets_peppol, storecove')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('label')
                                    ->label('Label (optional)')
                                    ->placeholder('e.g., Production, Staging')
                                    ->columnSpan(2),

                                TextInput::make('merchant_key')
                                    ->label('Key')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('merchant_value')
                                    ->label('Value')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
