<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddCoversClassAttributeRector;

return RectorConfig::configure()
    ->withSkip([
        '*/Modules/*/Http/*',
    ])
    ->withPaths([
        __DIR__ . '/Modules',
    ])
    ->withRules([
        AddCoversClassAttributeRector::class,
    ])
    ->withImportNames();
