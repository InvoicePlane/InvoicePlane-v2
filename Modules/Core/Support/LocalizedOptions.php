<?php

namespace Modules\Core\Support;

use Locale;

class LocalizedOptions
{
    /** @return array<string, string> */
    public static function languages(?string $locale = null): array
    {
        return self::localize(config('languages', []), $locale ?? app()->getLocale(), false);
    }

    /** @return array<string, string> */
    public static function countries(?string $locale = null): array
    {
        return self::localize(config('countries', []), $locale ?? app()->getLocale(), true);
    }

    /**
     * @param array<string, string> $options
     *
     * @return array<string, string>
     */
    private static function localize(array $options, string $locale, bool $regions): array
    {
        if ($locale === '' || preg_match('/^en(?:[_-]|$)/i', $locale) || ! class_exists(Locale::class)) {
            return $options;
        }

        foreach ($options as $code => $original) {
            if ( ! is_string($code) || ! is_string($original)) {
                continue;
            }

            if ($regions) {
                // ZZ is ICU's unknown-region marker, not a selectable country code.
                if ( ! preg_match('/^[A-Z]{2}$/', $code) || $code === 'ZZ') {
                    continue;
                }

                $label = Locale::getDisplayRegion('und_' . $code, $locale);
            } else {
                if ( ! preg_match('/^[a-z]{2,3}(?:[_-][A-Za-z]{2,4})?$/', $code)) {
                    continue;
                }

                // ICU returns unknown language codes verbatim, even in a compound locale.
                $language = Locale::getDisplayLanguage($code, 'en');
                if ( ! $language || $language === Locale::getPrimaryLanguage($code)) {
                    continue;
                }

                $label = Locale::getDisplayName($code, $locale);
            }

            if (is_string($label) && $label !== '' && $label !== $code) {
                $options[$code] = $label;
            }
        }

        return $options;
    }
}
