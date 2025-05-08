<?php

declare(strict_types=1);

use App\ImportModelIfMissingRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withImportNames()
    ->withSkip([
        '*/Modules/*/Http/*',
    ])
    ->withPaths([
        __DIR__ . '/Modules',
    ])
    ->withRules([
        ImportModelIfMissingRector::class,
    ]);
