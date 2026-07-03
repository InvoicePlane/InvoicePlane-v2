<?php

declare(strict_types=1);

namespace TestHonesty\Logging;

final class RateLimitLogger extends FileLogger
{
    public function __construct()
    {
        parent::__construct('rate_limit.log');
    }
}
