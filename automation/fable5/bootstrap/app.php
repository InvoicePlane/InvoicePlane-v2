<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Dotenv\Dotenv;

require __DIR__ . '/../../vendor/autoload.php';

$appBasePath = __DIR__ . '/../../';

/*
|--------------------------------------------------------------------------
| Load .env (CRITICAL for CLI automation)
|--------------------------------------------------------------------------
*/

if (file_exists($appBasePath . '.env')) {
    $dotenv = Dotenv::createImmutable($appBasePath);
    $dotenv->load();
}

/*
|--------------------------------------------------------------------------
| Container bootstrap
|--------------------------------------------------------------------------
*/

$container = new Container();

Container::setInstance($container);

$container->instance(Container::class, $container);

/*
|--------------------------------------------------------------------------
| Config loading
|--------------------------------------------------------------------------
*/

$configPath = $appBasePath . 'config';

$files = new Filesystem();

$configItems = [];

foreach ($files->files($configPath) as $file) {
    $configItems[$file->getBasename('.php')] = require $file->getPathname();
}

$config = new Config($configItems);

$container->instance(ConfigRepository::class, $config);
$container->instance('config', $config);

/*
|--------------------------------------------------------------------------
| Events (required by HTTP client + internal components)
|--------------------------------------------------------------------------
*/

$container->instance('events', new Dispatcher($container));

/*
|--------------------------------------------------------------------------
| HTTP client binding (Laravel Http facade support)
|--------------------------------------------------------------------------
*/

$container->singleton('http', function ($app) {
    return new HttpFactory($app);
});

/*
|--------------------------------------------------------------------------
| Facade wiring (IMPORTANT)
|--------------------------------------------------------------------------
*/

Http::setFacadeApplication($container);

/*
|--------------------------------------------------------------------------
| Helper access
|--------------------------------------------------------------------------
*/

return $container;
