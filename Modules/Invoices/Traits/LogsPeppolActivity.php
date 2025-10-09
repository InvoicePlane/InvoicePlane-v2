<?php

namespace Modules\Invoices\Traits;

use Illuminate\Support\Facades\Log;

trait LogsPeppolActivity
{
    protected function logPeppol(string $level, string $message, array $context = []): void
    {
        $context = array_merge([
            'component' => static::class,
        ], $context);

        Log::{$level}("[Peppol] {$message}", $context);
    }

    protected function logPeppolInfo(string $message, array $context = []): void
    {
        $this->logPeppol('info', $message, $context);
    }

    protected function logPeppolError(string $message, array $context = []): void
    {
        $this->logPeppol('error', $message, $context);
    }

    protected function logPeppolWarning(string $message, array $context = []): void
    {
        $this->logPeppol('warning', $message, $context);
    }

    protected function logPeppolDebug(string $message, array $context = []): void
    {
        $this->logPeppol('debug', $message, $context);
    }
}
