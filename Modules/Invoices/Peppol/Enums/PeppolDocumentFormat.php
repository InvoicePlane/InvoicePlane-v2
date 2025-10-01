<?php

namespace Modules\Invoices\Peppol\Enums;

/**
 * PeppolDocumentFormat - Enum for Peppol document formats.
 *
 * Different countries and sectors use different electronic invoice formats.
 * This enum defines the supported formats for Peppol transmission.
 *
 * @package Modules\Invoices\Peppol\Enums
 */
enum PeppolDocumentFormat: string
{
    /**
     * Universal Business Language 2.1 - Most common format for Peppol.
     * Used widely across Europe.
     */
    case UBL = 'ubl';

    /**
     * Cross Industry Invoice - Alternative format, common in Germany and France.
     */
    case CII = 'cii';

    /**
     * Get the human-readable label for the format.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::UBL => 'UBL 2.1 (Universal Business Language)',
            self::CII => 'CII (Cross Industry Invoice)',
        };
    }

    /**
     * Get the description for the format.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::UBL => 'Most widely used format across Europe. Recommended for most use cases.',
            self::CII => 'Common in Germany, France, and some other European countries.',
        };
    }

    /**
     * Get the recommended format based on country code.
     *
     * @param string|null $countryCode ISO 3166-1 alpha-2 country code
     * @return self
     */
    public static function recommendedForCountry(?string $countryCode): self
    {
        return match (strtoupper($countryCode ?? '')) {
            'DE', 'FR', 'AT' => self::CII,
            default => self::UBL,
        };
    }
}
