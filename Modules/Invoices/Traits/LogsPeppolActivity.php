<?php

namespace Modules\Invoices\Traits;

use Illuminate\Support\Facades\Log;

trait LogsPeppolActivity
{
    /**
     * Record a Peppol-related log entry at the given level with a standardized prefix and component context.
     *
     * The message is logged prefixed with "[Peppol]" and the provided context is merged with a `component`
     * field set to the implementing class name.
     *
     * @param string $level The log level name (e.g., "info", "error", "warning", "debug").
     * @param string $message The log message (without the "[Peppol]" prefix).
     * @param array $context Additional context data to include with the log; merged with the `component` key.
     */
    protected function logPeppol(string $level, string $message, array $context = []): void
    {
        $context = array_merge([
            'component' => static::class,
        ], $context);

        Log::{$level}("[Peppol] {$message}", $context);
    }

    /**
     * Log a Peppol informational message.
     *
     * @param string $message The message to record.
     * @param array $context Additional context to include in the log entry; merged with the default Peppol context.
     */
    protected function logPeppolInfo(string $message, array $context = []): void
    {
        $this->logPeppol('info', $message, $context);
    }

    /**
     * Log a Peppol-related error message.
     *
     * @param string $message The error message to record.
     * @param array $context Optional additional context; merged with a default `component` key identifying the implementing class.
     */
    protected function logPeppolError(string $message, array $context = []): void
    {
        $this->logPeppol('error', $message, $context);
    }

    /**
     * Log a Peppol-related message with warning severity.
     *
     * The provided context is merged with a `component` entry containing the implementing class name.
     *
     * @param string $message The log message.
     * @param array $context Additional contextual data to include with the log entry.
     */
    protected function logPeppolWarning(string $message, array $context = []): void
    {
        $this->logPeppol('warning', $message, $context);
    }

    /**
     * Log a Peppol debug message.
     *
     * The provided context will be merged with a `component` field set to the implementing class.
     *
     * @param string $message The log message.
     * @param array  $context Additional context to include with the log entry.
     */
    protected function logPeppolDebug(string $message, array $context = []): void
    {
        $this->logPeppol('debug', $message, $context);
    }
}