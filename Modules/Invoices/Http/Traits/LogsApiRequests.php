<?php

namespace Modules\Invoices\Http\Traits;

use Illuminate\Support\Facades\Log;

/**
 * LogsApiRequests - Trait for logging API requests and responses.
 *
 * Provides consistent logging functionality for API clients with
 * automatic sensitive data sanitization.
 */
trait LogsApiRequests
{
    /**
     * Whether to log requests and responses.
     *
     * @var bool
     */
    protected bool $loggingEnabled = false;

    /**
     * Enable request logging.
     *
     * @return $this
     */
    public function enableLogging(): self
    {
        $this->loggingEnabled = true;

        return $this;
    }

    /**
     * Disable request logging.
     *
     * @return $this
     */
    public function disableLogging(): self
    {
        $this->loggingEnabled = false;

        return $this;
    }

    /**
     * Log an API request.
     *
     * @param string               $method
     * @param string               $uri
     * @param array<string, mixed> $options
     *
     * @return void
     */
    protected function logRequest(string $method, string $uri, array $options): void
    {
        if ( ! $this->loggingEnabled) {
            return;
        }

        Log::info('HTTP Request', [
            'method'  => $method,
            'uri'     => $uri,
            'options' => $this->sanitizeForLogging($options),
        ]);
    }

    /**
     * Log an API response.
     *
     * @param string $method
     * @param string $uri
     * @param int    $status
     * @param mixed  $body
     *
     * @return void
     */
    protected function logResponse(string $method, string $uri, int $status, mixed $body): void
    {
        if ( ! $this->loggingEnabled) {
            return;
        }

        Log::info('HTTP Response', [
            'method' => $method,
            'uri'    => $uri,
            'status' => $status,
            'body'   => $body,
        ]);
    }

    /**
     * Log an API error.
     *
     * @param string               $type    Error type (Connection, Request, Unexpected)
     * @param string               $method
     * @param string               $uri
     * @param string               $message
     * @param array<string, mixed> $context Additional context
     *
     * @return void
     */
    protected function logError(string $type, string $method, string $uri, string $message, array $context = []): void
    {
        Log::error("HTTP {$type} Error", array_merge([
            'method'  => $method,
            'uri'     => $uri,
            'message' => $message,
        ], $context));
    }

    /**
     * Sanitize data for logging by redacting sensitive information.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function sanitizeForLogging(array $data): array
    {
        $sanitized = $data;

        // Redact sensitive headers
        if (isset($sanitized['headers'])) {
            $sensitiveHeaders = ['Authorization', 'X-API-Key', 'X-Auth-Token'];
            foreach ($sensitiveHeaders as $header) {
                if (isset($sanitized['headers'][$header])) {
                    $sanitized['headers'][$header] = '***REDACTED***';
                }
            }
        }

        // Redact auth credentials
        if (isset($sanitized['auth'])) {
            $sanitized['auth'] = ['***REDACTED***', '***REDACTED***'];
        }

        if (isset($sanitized['bearer'])) {
            $sanitized['bearer'] = '***REDACTED***';
        }

        if (isset($sanitized['digest'])) {
            $sanitized['digest'] = ['***REDACTED***', '***REDACTED***'];
        }

        return $sanitized;
    }
}
