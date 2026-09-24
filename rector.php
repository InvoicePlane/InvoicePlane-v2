<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withImportNames()
    ->withSkip([
    ])
    ->withPaths([
        __DIR__ . '/Modules',
    ])
    ->withRules([
    ]);
