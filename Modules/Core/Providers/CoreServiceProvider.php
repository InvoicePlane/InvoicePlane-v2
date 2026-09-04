<?php

namespace Modules\Core\Providers;

use Awcodes\Mason\Support\IframeRenderer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Models\Company;
use Modules\Core\Observers\CompanyObserver;
use Modules\Core\ReportBuilder\ReportIframeRenderer;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));

        Company::observe(CompanyObserver::class);
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        /*
         * Mason's preview iframe must paint report bricks with their
         * builder previews, not with the print rendering. MasonController
         * resolves the renderer out of the container, so swapping the
         * binding is enough.
         */
        $this->app->bind(
            IframeRenderer::class,
            fn ($app, array $parameters): ReportIframeRenderer => new ReportIframeRenderer(
                is_array($parameters['blocks'] ?? null) ? $parameters['blocks'] : [],
            ),
        );
    }

    public function registerViews(): void
    {
        $viewPath   = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);

        /*
         * awcodes/mason still registers its views under the 'mason' namespace
         * (vendor code, not ours to rename) — Laravel would normally pick up
         * an override for that automatically from resources/views/vendor/mason,
         * but our override now lives at Modules/Core/resources/views/vendor/
         * report-builder, a path/name Laravel's automatic vendor-override
         * convention has no way to find. Register it explicitly instead.
         * prependNamespace (not addNamespace) is required so this override is
         * checked before the package's own views regardless of provider boot
         * order.
         */
        View::prependNamespace('mason', module_path($this->name, 'resources/views/vendor/report-builder'));
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

    public function provides(): array
    {
        return [];
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Core\Commands\MigrateV1Command::class,
            \Modules\Core\Commands\MakeUserCommand::class,
            \Modules\Core\Commands\GenerateObservers::class,
            \Modules\Core\Console\ReportsSyncSystemCommand::class,
        ]);
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
