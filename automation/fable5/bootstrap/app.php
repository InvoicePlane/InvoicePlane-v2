<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;

require __DIR__ . '/../../vendor/autoload.php';

$appBasePath = __DIR__ . '/../../';

$container = new Container();

Container::setInstance($container);

/*
|--------------------------------------------------------------------------
| Basic container bindings
|--------------------------------------------------------------------------
*/

$container->instance(Container::class, $container);

/*
|--------------------------------------------------------------------------
| Config loading (no kernel)
|--------------------------------------------------------------------------
*/

$configPath = $appBasePath . 'config';

$files = new Filesystem();

$configItems = [];

foreach ($files->files($configPath) as $file) {
    $key = basename($file->getFilename(), '.php');

    $configItems[$key] = require $file->getPathname();
}

$config = new Config($configItems);

$container->instance(ConfigRepository::class, $config);
$container->instance('config', $config);

/*
|--------------------------------------------------------------------------
| Event dispatcher (required by some Laravel components)
|--------------------------------------------------------------------------
*/

$container->instance('events', new Dispatcher($container));

/*
|--------------------------------------------------------------------------
| HTTP Client (Laravel Http facade backing)
|--------------------------------------------------------------------------
*/

$container->singleton('http', function ($app) {
    return new HttpFactory($app);
});

/*
|--------------------------------------------------------------------------
| Helper accessor
|--------------------------------------------------------------------------
*/

return $container;
