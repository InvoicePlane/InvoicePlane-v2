<?php

namespace Modules\Invoices\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Models\Schedule;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Observers\InvoiceItemObserver;
use Modules\Invoices\Observers\InvoiceObserver;
use Modules\Quotes\Providers\EventServiceProvider;
use Modules\Quotes\Providers\RouteServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class InvoicesServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Invoices';

    protected string $nameLower = 'invoices';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Register Peppol HTTP clients and services
        $this->registerPeppolServices();
    }

    /**
     * Register Peppol-related services and dependencies.
     *
     * This method sets up the HTTP client infrastructure and Peppol services
     * with proper dependency injection and exception handling.
     *
     * @return void
     */
    protected function registerPeppolServices(): void
    {
        // Register ApiClient
        $this->app->bind(
            \Modules\Invoices\Http\Clients\ApiClient::class,
            function ($app) {
                return new \Modules\Invoices\Http\Clients\ApiClient();
            }
        );

        // Register HttpClientExceptionHandler as a decorator
        $this->app->bind(
            \Modules\Invoices\Http\Decorators\HttpClientExceptionHandler::class,
            function ($app) {
                $apiClient = $app->make(\Modules\Invoices\Http\Clients\ApiClient::class);
                $handler = new \Modules\Invoices\Http\Decorators\HttpClientExceptionHandler($apiClient);
                
                // Enable logging in non-production environments
                if (!$app->environment('production')) {
                    $handler->enableLogging();
                }
                
                return $handler;
            }
        );

        // Register DocumentsClient for e-invoice.be
        $this->app->bind(
            \Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient::class,
            function ($app) {
                $handler = $app->make(\Modules\Invoices\Http\Decorators\HttpClientExceptionHandler::class);
                
                // Get configuration from environment or config
                $apiKey = config('invoices.peppol.e_invoice_be.api_key');
                $baseUrl = config('invoices.peppol.e_invoice_be.base_url');
                
                return new \Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient(
                    $handler,
                    $apiKey,
                    $baseUrl
                );
            }
        );

        // Register PeppolService
        $this->app->bind(
            \Modules\Invoices\Peppol\Services\PeppolService::class,
            function ($app) {
                $documentsClient = $app->make(\Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient::class);
                return new \Modules\Invoices\Peppol\Services\PeppolService($documentsClient);
            }
        );

        // Register SendInvoiceToPeppolAction
        $this->app->bind(
            \Modules\Invoices\Actions\SendInvoiceToPeppolAction::class,
            function ($app) {
                $peppolService = $app->make(\Modules\Invoices\Peppol\Services\PeppolService::class);
                return new \Modules\Invoices\Actions\SendInvoiceToPeppolAction($peppolService);
            }
        );
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'resources/lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'resources/lang'));
        }
    }

    public function registerViews(): void
    {
        $viewPath   = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    public function provides(): array
    {
        return [];
    }

    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    protected function registerConfig(): void
    {
        $relativeConfigPath = config('modules.paths.generator.config.path');
        $configPath         = module_path($this->name, $relativeConfigPath);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $configKey    = $this->nameLower . '.' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $relativePath);
                    $key          = ($relativePath === 'config.php') ? $this->nameLower : $configKey;

                    $this->publishes([$file->getPathname() => config_path($relativePath)], 'config');
                    $this->mergeConfigFrom($file->getPathname(), $key);
                }
            }
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->nameLower)) {
                $paths[] = $path . '/modules/' . $this->nameLower;
            }
        }

        return $paths;
    }
}
