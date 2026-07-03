<?php

declare(strict_types=1);

namespace Fable\Support;

final class Environment
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}
