<?php

namespace Modules\Core\Support;

use Illuminate\Support\Carbon;

class DateHelpers
{
    /**
     * Format a date as a localized string.
     */
    public static function formatDate($date): string
    {
        if ( ! $date) {
            return '-';
        }

        return ($date instanceof Carbon ? $date : Carbon::parse($date))->format('Y-m-d');
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
}
