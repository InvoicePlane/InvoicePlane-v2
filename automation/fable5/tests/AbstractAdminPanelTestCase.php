<?php

declare(strict_types=1);

namespace Fable\Tests;

abstract class AbstractAdminPanelTestCase extends TestCase
{
    protected function getFixture(string $name): array
    {
        $path = __DIR__.'/Fixtures/'.$name.'.json';
        if (! file_exists($path)) {
            throw new \InvalidArgumentException("Fixture not found: {$path}");
        }

        return json_decode(file_get_contents($path), true);
    }
}
