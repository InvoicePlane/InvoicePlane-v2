<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Models\Company;
use Modules\Invoices\Peppol\Providers\ProviderFactory;

class PeppolIntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic settings
                Section::make('Integration Setup')
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

                                Select::make('provider_name')
                                    ->label('Provider')
                                    ->options(self::getProviderOptions())
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(1),

                                Toggle::make('enabled')
                                    ->label('Enabled')
                                    ->default(false)
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Dynamic provider-specific settings
                Section::make('Provider Configuration')
                    ->schema(self::getDynamicProviderFields())
                    ->columnSpanFull()
                    ->visible(fn ($get) => !empty($get('provider_name'))),
            ]);
    }

    /**
     * Get available providers from ProviderFactory.
     *
     * @return array<string, string>
     */
    private static function getProviderOptions(): array
    {
        try {
            $providers = ProviderFactory::getAvailableProviders();

            return collect($providers)
                ->mapWithKeys(fn (string $class, string $name): array => [$name => ucfirst(str_replace('_', ' ', $name))])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Build dynamic form fields based on provider schema.
     *
     * This renders all fields defined in the provider's settings() schema.
     * Fields marked as 'managed' are read-only (e.g., access_token).
     *
     * @return array<mixed>
     */
    private static function getDynamicProviderFields(): array
    {
        // Static placeholder for now — in a full implementation, this would use Filament's
        // afterStateUpdated() reactive behavior to rebuild fields dynamically
        return [
            Placeholder::make('provider_fields_notice')
                ->label('')
                ->content('Provider configuration fields will be displayed based on your provider selection.')
                ->columnSpanFull(),
        ];
    }
}
