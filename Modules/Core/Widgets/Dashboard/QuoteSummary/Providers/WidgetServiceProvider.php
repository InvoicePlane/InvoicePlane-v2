<?php

namespace Modules\Core\Widgets\Dashboard\QuoteSummary\Providers;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Providers\WidgetServiceProvider;

use Modules\Core\Filament\Company\Pages\Dashboard;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummaryWidgetComposer;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Controllers\WidgetController;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummarySettingComposer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register the view path.
        view()->addLocation(app_path('Widgets/Dashboard/QuoteSummary/Views'));

        // Register the widget view composer.
        view()->composer('QuoteSummaryWidget', 'Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummaryWidgetComposer');

        // Register the setting view composer.
        view()->composer('QuoteSummaryWidgetSettings', 'Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummarySettingComposer');

        // Widgets don't have route files so we'll place this here.
        Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'Modules\Core\Widgets\Dashboard\QuoteSummary\Controllers'], function (): void {
            Route::post('widgets/dashboard/quote_summary/render_partial', ['uses' => 'WidgetController@renderPartial', 'as' => 'widgets.dashboard.quoteSummary.renderPartial']);
        });
    }

    public function register(): void {}
}
