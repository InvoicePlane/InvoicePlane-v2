<?php

namespace Modules\Invoices\Peppol\Enums;

/**
 * PeppolEndpointScheme - Enum for Peppol participant identifier schemes.
 *
 * Each country or region uses different identifier schemes for Peppol participants.
 * This enum defines the common schemes used across Europe and internationally.
 *
 * @see https://docs.peppol.eu/edelivery/codelists/Peppol%20Code%20Lists%20-%20Participant%20identifier%20schemes.html
 */
enum PeppolEndpointScheme: string
{
    /**
     * Belgian Company Number (CBE/KBO/BCE).
     * Format: 10 digits (e.g., BE:0123456789).
     */
    case BE_CBE = 'BE:CBE';

    /**
     * German VAT Number (Umsatzsteuer-Identifikationsnummer).
     * Format: DE + 9 digits.
     */
    case DE_VAT = 'DE:VAT';

    /**
     * French SIREN/SIRET Number.
     * Format: 9 or 14 digits.
     */
    case FR_SIRENE = 'FR:SIRENE';

    /**
     * Italian VAT Number (Partita IVA).
     * Format: IT + 11 digits.
     */
    case IT_VAT = 'IT:VAT';

    /**
     * Italian Codice Fiscale (Tax Code).
     * Format: 16 alphanumeric characters.
     */
    case IT_CF = 'IT:CF';

    /**
     * Spanish NIF/CIF (Tax Identification Number).
     * Format: 9 characters (letter + 7-8 digits + letter/digit).
     */
    case ES_VAT = 'ES:VAT';

    /**
     * Dutch KVK Number (Chamber of Commerce).
     * Format: 8 digits.
     */
    case NL_KVK = 'NL:KVK';

    /**
     * Norwegian Organization Number.
     * Format: 9 digits.
     */
    case NO_ORGNR = 'NO:ORGNR';

    /**
     * Danish CVR Number (Central Business Register).
     * Format: 8 digits.
     */
    case DK_CVR = 'DK:CVR';

    /**
     * Swedish Organization Number (Organisationsnummer).
     * Format: 10 digits (NNNNNN-NNNN).
     */
    case SE_ORGNR = 'SE:ORGNR';

    /**
     * Finnish Business ID (Y-tunnus).
     * Format: 7 digits + hyphen + check digit.
     */
    case FI_OVT = 'FI:OVT';

    /**
     * Austrian UID Number (Umsatzsteuer-Identifikationsnummer).
     * Format: ATU + 8 digits.
     */
    case AT_VAT = 'AT:VAT';

    /**
     * Swiss UID/IDE/IDI (Business Identification Number).
     * Format: CHE + 9 digits.
     */
    case CH_UIDB = 'CH:UIDB';

    /**
     * UK Companies House Number.
     * Format: 8 characters.
     */
    case GB_COH = 'GB:COH';

    /**
     * Global Location Number (GLN) - International.
     * Format: 13 digits.
     */
    case GLN = '0088';

    /**
     * DUNS Number (Data Universal Numbering System) - International.
     * Format: 9 digits.
     */
    case DUNS = '0060';

    /**
     * International Organization for Standardization 6523 (ICD 0002).
     * Used for international identification.
     */
    case ISO_6523 = '0002';

    /**
     * Get the recommended scheme for a country code.
     *
     * @param string|null $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return self
     */
    public static function forCountry(?string $countryCode): self
    {
        return match (mb_strtoupper($countryCode ?? '')) {
            'BE'    => self::BE_CBE,
            'DE'    => self::DE_VAT,
            'FR'    => self::FR_SIRENE,
            'IT'    => self::IT_VAT,
            'ES'    => self::ES_VAT,
            'NL'    => self::NL_KVK,
            'NO'    => self::NO_ORGNR,
            'DK'    => self::DK_CVR,
            'SE'    => self::SE_ORGNR,
            'FI'    => self::FI_OVT,
            'AT'    => self::AT_VAT,
            'CH'    => self::CH_UIDB,
            'GB'    => self::GB_COH,
            default => self::ISO_6523,
        };
    }

    /**
     * Get the human-readable label for the scheme.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::BE_CBE    => 'Belgian CBE/KBO/BCE Number',
            self::DE_VAT    => 'German VAT Number',
            self::FR_SIRENE => 'French SIREN/SIRET',
            self::IT_VAT    => 'Italian VAT Number (Partita IVA)',
            self::IT_CF     => 'Italian Tax Code (Codice Fiscale)',
            self::ES_VAT    => 'Spanish NIF/CIF',
            self::NL_KVK    => 'Dutch KVK Number',
            self::NO_ORGNR  => 'Norwegian Organization Number',
            self::DK_CVR    => 'Danish CVR Number',
            self::SE_ORGNR  => 'Swedish Organization Number',
            self::FI_OVT    => 'Finnish Business ID',
            self::AT_VAT    => 'Austrian UID Number',
            self::CH_UIDB   => 'Swiss UID Number',
            self::GB_COH    => 'UK Companies House Number',
            self::GLN       => 'Global Location Number (GLN)',
            self::DUNS      => 'DUNS Number',
            self::ISO_6523  => 'ISO 6523 (ICD 0002)',
        };
    }

    /**
     * Get the description for the scheme.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::BE_CBE    => 'Belgian Crossroads Bank for Enterprises number (10 digits)',
            self::DE_VAT    => 'German VAT identification number (DE + 9 digits)',
            self::FR_SIRENE => 'French business registry number (9 or 14 digits)',
            self::IT_VAT    => 'Italian VAT number (IT + 11 digits)',
            self::IT_CF     => 'Italian fiscal code for individuals and companies (16 characters)',
            self::ES_VAT    => 'Spanish tax identification number (9 characters)',
            self::NL_KVK    => 'Dutch Chamber of Commerce number (8 digits)',
            self::NO_ORGNR  => 'Norwegian business registry number (9 digits)',
            self::DK_CVR    => 'Danish Central Business Register number (8 digits)',
            self::SE_ORGNR  => 'Swedish organization number (10 digits)',
            self::FI_OVT    => 'Finnish business identifier (7 digits + check digit)',
            self::AT_VAT    => 'Austrian VAT number (ATU + 8 digits)',
            self::CH_UIDB   => 'Swiss business identification number (CHE + 9 digits)',
            self::GB_COH    => 'UK Companies House registration number',
            self::GLN       => 'International Global Location Number (13 digits)',
            self::DUNS      => 'International Data Universal Numbering System (9 digits)',
            self::ISO_6523  => 'International ISO 6523 identifier',
        };
    }

    /**
     * Validate identifier format for this scheme.
     *
     * @param string $identifier The identifier to validate
     *
     * @return bool
     */
    public function validates(string $identifier): bool
    {
        $identifier = mb_trim($identifier);

        return match ($this) {
            self::BE_CBE    => (bool) preg_match('/^\d{10}$/', $identifier),
            self::DE_VAT    => (bool) preg_match('/^DE\d{9}$/', $identifier),
            self::FR_SIRENE => (bool) preg_match('/^\d{9}(\d{5})?$/', $identifier),
            self::IT_VAT    => (bool) preg_match('/^IT\d{11}$/', $identifier),
            self::IT_CF     => (bool) preg_match('/^[A-Z0-9]{16}$/', mb_strtoupper($identifier)),
            self::ES_VAT    => (bool) preg_match('/^[A-Z]\d{7,8}[A-Z0-9]$/', mb_strtoupper($identifier)),
            self::NL_KVK    => (bool) preg_match('/^\d{8}$/', $identifier),
            self::NO_ORGNR  => (bool) preg_match('/^\d{9}$/', $identifier),
            self::DK_CVR    => (bool) preg_match('/^\d{8}$/', $identifier),
            self::SE_ORGNR  => (bool) preg_match('/^\d{6}-?\d{4}$/', $identifier),
            self::FI_OVT    => (bool) preg_match('/^\d{7}-?\d$/', $identifier),
            self::AT_VAT    => (bool) preg_match('/^ATU\d{8}$/', $identifier),
            self::CH_UIDB   => (bool) preg_match('/^CHE[-.\s]?\d{3}[-.\s]?\d{3}[-.\s]?\d{3}$/', $identifier),
            self::GB_COH    => (bool) preg_match('/^[A-Z0-9]{8}$/', mb_strtoupper($identifier)),
            self::GLN       => (bool) preg_match('/^\d{13}$/', $identifier),
            self::DUNS      => (bool) preg_match('/^\d{9}$/', $identifier),
            self::ISO_6523  => mb_strlen($identifier) > 0, // Flexible validation
        };
    }

    /**
     * Format identifier according to scheme rules.
     *
     * @param string $identifier The raw identifier
     *
     * @return string Formatted identifier
     */
    public function format(string $identifier): string
    {
        $identifier = mb_trim($identifier);

        return match ($this) {
            self::SE_ORGNR => preg_replace('/^(\d{6})(\d{4})$/', '$1-$2', $identifier) ?? $identifier,
            self::FI_OVT   => preg_replace('/^(\d{7})(\d)$/', '$1-$2', $identifier) ?? $identifier,
            default        => $identifier,
        };
    }
}
