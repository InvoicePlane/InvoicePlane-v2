<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Throwable;

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
                                    ->live()
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
                    ->schema(fn (Get $get): array => self::getDynamicProviderFields($get('provider_name')))
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => ! empty($get('provider_name'))),
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
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Build one credential input per key the selected provider declares in settings(),
     * excluding managedSettingsKeys() (system-managed state like an OAuth2 access_token
     * that an admin should never type in — see ProviderInterface::managedSettingsKeys()).
     *
     * @return array<mixed>
     */
    private static function getDynamicProviderFields(?string $providerName): array
    {
        if (empty($providerName)) {
            return [];
        }

        try {
            $providerClass = ProviderFactory::getProviderClass($providerName);
        } catch (Throwable $e) {
            return [];
        }

        $keys = array_diff($providerClass::settings(), $providerClass::managedSettingsKeys());

        return collect($keys)
            ->map(function (string $key) use ($providerName) {
                // Not a real model column — reaches PeppolManagementService::createIntegration()/
                // updateIntegration() through the same $data array as company_id/provider_name,
                // which filters it down to just the provider's declared credential keys before
                // persisting via setConfig().
                $field = TextInput::make($key)
                    ->label(ucfirst(str_replace('_', ' ', $key)))
                    ->default(function (?PeppolIntegration $record) use ($key, $providerName) {
                        if ( ! $record || $record->provider_name !== $providerName) {
                            return null;
                        }

                        return $record->getConfigValue($key);
                    });

                if (str_contains($key, 'key') || str_contains($key, 'secret') || str_contains($key, 'token')) {
                    $field = $field->password()->revealable();
                }

                return $field;
            })
            ->all();
    }
}
