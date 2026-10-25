<?php

namespace Modules\Core\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Traits\Localizable;
use Modules\Core\Models\Setting;

class DocumentFormatter
{
    use Localizable;

    public function __construct(public readonly string $locale, private readonly ?string $dateFormat = null) {}

    public static function resolveLocale(?string $language): string
    {
        return $language !== null && array_key_exists($language, config('languages', []))
            ? $language
            : app()->getLocale();
    }

    public static function forCompany(int $companyId, ?string $language): self
    {
        $format = app(DisplaySettings::class)->get($companyId, Setting::KEY_DATE_FORMAT);

        return new self(self::resolveLocale($language), $format ?: null);
    }

    public function amount(int|float $value): string
    {
        return Number::format($value, precision: 2, locale: $this->locale);
    }

    public function quantity(int|float $value): string
    {
        return Number::format($value, maxPrecision: 8, locale: $this->locale);
    }

    public function date(DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $date   = $value instanceof DateTimeInterface ? Carbon::instance($value)->copy() : Carbon::parse($value);
        $format = $this->dateFormat ?: (str_starts_with($this->locale, 'de') ? 'd.m.Y' : 'Y-m-d');

        return $date->locale($this->locale)->translatedFormat($format);
    }
}
