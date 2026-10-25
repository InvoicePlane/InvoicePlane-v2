<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Core\Models\Setting;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Support\DisplaySettings;
use Modules\Invoices\Models\Invoice;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(DisplaySettings::class);

        $this->app->bind(
            LoginResponseContract::class,
            LoginResponse::class
        );
    }

    public function boot(): void
    {
        $this->configureDisplayFormatting();

        Relation::morphMap([
            'invoice' => Invoice::class,
        ]);

        if ( ! app()->isLocal()) {
            URL::forceScheme('https');
        }
    }

    private static function displayCurrency(): string
    {
        $currency = app(DisplaySettings::class)->get(Filament::getTenant()?->getKey(), Setting::KEY_CURRENCY_CODE);

        return $currency
            ?: config('ip.currency_code')
            ?: Number::defaultCurrency();
    }

    private function configureDisplayFormatting(): void
    {
        $invalidateSettings = static function (): void {
            app(DisplaySettings::class)->clear();
        };
        Setting::saved($invalidateSettings);
        Setting::deleted($invalidateSettings);

        // Resolve these defaults when a component renders: a tenant and locale
        // are not yet selected when application service providers boot.
        $configure = static function (Table|Schema $component): void {
            $component
                ->defaultDateDisplayFormat(fn (): string => DateHelpers::resolveDateFormat())
                ->defaultDateTimeDisplayFormat(fn (): string => DateHelpers::resolveDateFormat() . ' H:i:s')
                ->defaultNumberLocale(fn (): string => app()->getLocale())
                ->defaultCurrency(fn (): string => self::displayCurrency());
        };

        Table::configureUsing($configure);
        Schema::configureUsing($configure);

        // Only display defaults change; HTML/native and stored date formats
        // continue using Filament's existing ISO formats.
        DateTimePicker::configureUsing(static function (DateTimePicker $picker): void {
            $picker
                ->defaultDateDisplayFormat(fn (): string => DateHelpers::resolveDateFormat())
                ->defaultDateTimeDisplayFormat(fn (): string => DateHelpers::resolveDateFormat() . ' H:i')
                ->defaultDateTimeWithSecondsDisplayFormat(fn (): string => DateHelpers::resolveDateFormat() . ' H:i:s');
        });
    }
}
