<?php

namespace Modules\Core\Support;

use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Modules\Core\Models\Setting;

class DateHelpers
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * Format a date as a localized string, honouring the admin-configured
     * `settings.date_format` (see `config('ip.date_formats')` for the
     * available options), falling back to the current locale’s date format when unset.
     */
    public static function formatDate($date): string
    {
        if ( ! $date) {
            return '-';
        }

        return ($date instanceof CarbonInterface ? $date->copy() : Carbon::parse($date))
            ->locale(app()->getLocale())
            ->translatedFormat(static::resolveDateFormat());
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
        $carbon = ($date instanceof CarbonInterface ? $date->copy() : Carbon::parse($date))
            ->locale(app()->getLocale());
        $diff = now()->diffInDays($carbon, false);

        if (abs($diff) > $maxPeriod) {
            return self::formatDate($carbon);
        }

        return $carbon->diffForHumans(now(), [
            'parts'  => 1,
            'short'  => true,
            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
        ]);
    }

    /**
     * Resolve the active company’s date format, then the global setting.
     * The fallback is German day-month-year or the existing ISO format.
     */
    public static function resolveDateFormat(): string
    {
        $format = app(DisplaySettings::class)->get(Filament::getTenant()?->getKey(), Setting::KEY_DATE_FORMAT);

        return $format ?: (str_starts_with(app()->getLocale(), 'de') ? 'd.m.Y' : self::DEFAULT_DATE_FORMAT);
    }
}
