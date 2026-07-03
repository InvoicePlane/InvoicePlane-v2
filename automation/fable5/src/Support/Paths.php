<?php

declare(strict_types=1);

namespace Fable5\Support;

final class Paths
{
    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function src(): string
    {
        return self::root() . '/src';
    }

    public static function storage(): string
    {
        return self::root() . '/storage';
    }

    public static function config(): string
    {
        return self::root() . '/config';
    }
}
