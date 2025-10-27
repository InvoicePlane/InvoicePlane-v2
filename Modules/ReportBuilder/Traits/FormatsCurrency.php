<?php

namespace Modules\ReportBuilder\Traits;

/**
 * Trait for formatting currency values in report blocks.
 */
trait FormatsCurrency
{
    /**
     * Format a currency amount.
     *
     * @param float       $amount   The amount to format
     * @param string|null $currency The currency code (defaults to USD)
     *
     * @return string The formatted currency string
     */
    private function formatCurrency(float $amount, ?string $currency = null): string
    {
        $currency ??= 'USD';

        return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}
