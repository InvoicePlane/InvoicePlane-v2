<?php

namespace Modules\Core\Support;

use Illuminate\Support\Carbon;
use Modules\Core\Models\Setting;

class DateHelpers
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * Format a date as a localized string, honouring the admin-configured
     * `settings.date_format` (see `config('ip.date_formats')` for the
     * available options), falling back to Y-m-d when unset.
     */
    public static function formatDate($date): string
    {
        if ( ! $date) {
            return '-';
        }

        return ($date instanceof Carbon ? $date : Carbon::parse($date))->format(static::resolveDateFormat());
    }

    /**
     * Format a date as "since" (e.g. "3 days ago") or "in X days".
     * $maxPeriod: maximum days to show relative, else show date.
     */
    public static function formatSince($date, int $maxPeriod = 360): string
    {
        if ( ! $date) {
            return '-';
        }
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $diff   = now()->diffInDays($carbon, false);

        if (abs($diff) > $maxPeriod) {
            return self::formatDate($carbon);
        }

        return $carbon->diffForHumans(now(), [
            'parts'  => 1,
            'short'  => true,
            'syntax' => $diff < 0 ? Carbon::DIFF_RELATIVE_TO_NOW : Carbon::DIFF_RELATIVE_TO_NOW,
        ]);
    }

    /**
     * Resolve the configured date format from the `settings` table
     * (see `Modules\Core\Filament\Admin\Pages\Settings`), falling back
     * to Y-m-d when the setting has never been saved.
     */
    protected static function resolveDateFormat(): string
    {
        return Setting::getByKey('date_format') ?: self::DEFAULT_DATE_FORMAT;
    }
}
