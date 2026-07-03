<?php

declare(strict_types=1);

namespace TestHonesty\Logging;

final class RetryLogger extends FileLogger
{
    public function __construct()
    {
        parent::__construct('retry.log');
    }
}
