<?php

namespace Modules\Invoices\Peppol\Enums;

/**
 * PeppolDocumentFormat - Enum for electronic invoice formats.
 *
 * Different countries and sectors use different electronic invoice formats.
 * This enum defines all supported formats for e-invoicing transmission.
 *
 * Based on InvoicePlane v1 XML templates implementation and European e-invoice standards.
 */
enum PeppolDocumentFormat: string
{
    /**
     * Universal Business Language 2.1 - Most common format for Peppol.
     * Used widely across Europe. Default for most countries.
     */
    case UBL_21 = 'ubl_2.1';

    /**
     * UBL 2.4 - Updated version of Universal Business Language.
     * Enhanced features and validation rules.
     */
    case UBL_24 = 'ubl_2.4';

    /**
     * Cross Industry Invoice - Alternative format, common in Germany and France.
     * Part of the UN/CEFACT standard.
     */
    case CII = 'cii';

    /**
     * Facturae v3.2 - Spanish e-invoice format.
     * Mandatory for invoices to Spanish public administration.
     */
    case FACTURAE_32 = 'facturae_3.2';

    /**
     * FatturaPA v1.2 - Italian e-invoice format.
     * Mandatory for all invoices in Italy.
     */
    case FATTURAPA_12 = 'fatturapa_1.2';

    /**
     * Factur-X v1.0 - French/German hybrid format.
     * Combines PDF/A-3 with embedded XML (ZUGFeRD/CII).
     */
    case FACTURX = 'factur-x';

    /**
     * ZUGFeRD v1.0 - German e-invoice format.
     * PDF with embedded XML data.
     */
    case ZUGFERD_10 = 'zugferd_1.0';

    /**
     * ZUGFeRD v2.0 - Updated German e-invoice format.
     * Compatible with Factur-X, uses CII format.
     */
    case ZUGFERD_20 = 'zugferd_2.0';

    /**
     * OIOUBL - Danish e-invoice format.
     * Based on UBL with Danish-specific requirements.
     */
    case OIOUBL = 'oioubl';

    /**
     * EHF (Elektronisk Handelsformat) - Norwegian e-invoice format.
     * Based on UBL with Norwegian-specific requirements.
     */
    case EHF_30 = 'ehf_3.0';

    /**
     * PEPPOL BIS Billing 3.0 - Default Peppol format for most countries.
     */
    case PEPPOL_BIS_30 = 'peppol_bis_3.0';

    /**
     * Get the recommended format based on country code.
     *
     * @param string|null $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return self
     */
    public static function recommendedForCountry(?string $countryCode): self
    {
        return match (mb_strtoupper($countryCode ?? '')) {
            'DE', 'FR', 'AT'                       => self::CII,
            'IT'                                   => self::FATTURAPA_12,
            'ES'                                   => self::FACTURAE_32,
            'DK'                                   => self::OIOUBL,
            'NO'                                   => self::EHF_30,
            'NL', 'BE', 'GB', 'SE', 'FI', 'XX', '' => self::UBL_24,
            default                                => self::UBL_24,
        };
    }

    /**
     * Get all formats suitable for a given country.
     *
     * @param string|null $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return array<self>
     */
    public static function formatsForCountry(?string $countryCode): array
    {
        $country = mb_strtoupper($countryCode ?? '');

        return match ($country) {
            'AT'    => [self::CII, self::UBL_21],
            'DE'    => [self::ZUGFERD_20, self::ZUGFERD_10, self::CII, self::UBL_21],
            'DK'    => [self::OIOUBL, self::UBL_21],
            'ES'    => [self::FACTURAE_32, self::UBL_21],
            'FR'    => [self::FACTURX, self::CII, self::UBL_21],
            'IT'    => [self::FATTURAPA_12, self::UBL_21],
            'NO'    => [self::EHF_30, self::UBL_21],
            default => [self::UBL_21, self::CII],
        };
    }

    /**
     * Get the human-readable label for the format.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::UBL_21        => 'UBL 2.1',
            self::UBL_24        => 'UBL 2.4',
            self::CII           => 'Cross Industry Invoice (CII)',
            self::FACTURAE_32   => 'Facturae 3.2 (Spain)',
            self::FATTURAPA_12  => 'FatturaPA 1.2 (Italy)',
            self::FACTURX       => 'Factur-X (France/Germany)',
            self::ZUGFERD_10    => 'ZUGFeRD 1.0',
            self::ZUGFERD_20    => 'ZUGFeRD 2.0',
            self::OIOUBL        => 'OIOUBL (Denmark)',
            self::EHF_30        => 'EHF 3.0 (Norway)',
            self::PEPPOL_BIS_30 => 'PEPPOL BIS Billing 3.0',
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
            self::UBL_21        => 'Most widely used format across Europe. Recommended for most use cases.',
            self::UBL_24        => 'Updated UBL format with enhanced validation rules.',
            self::CII           => 'Common in Germany, France, and Austria. UN/CEFACT standard.',
            self::FACTURAE_32   => 'Mandatory for invoices to Spanish public administration.',
            self::FATTURAPA_12  => 'Mandatory format for all B2B and B2G invoices in Italy.',
            self::FACTURX       => 'Hybrid PDF/A-3 format with embedded XML. Used in France and Germany.',
            self::ZUGFERD_10    => 'German standard combining PDF with embedded XML invoice data.',
            self::ZUGFERD_20    => 'Updated ZUGFeRD compatible with Factur-X. Uses CII format.',
            self::OIOUBL        => 'Danish UBL-based format with national extensions.',
            self::EHF_30        => 'Norwegian EHF 3.0 format for PEPPOL network.',
            self::PEPPOL_BIS_30 => 'Default PEPPOL format for most countries. Based on UBL.',
        };
    }

    /**
     * Get the file extension for this format.
     *
     * @return string
     */
    public function extension(): string
    {
        return match ($this) {
            self::FACTURX, self::ZUGFERD_10, self::ZUGFERD_20 => 'pdf',
            default                                           => 'xml',
        };
    }

    /**
     * Check if this format requires PDF/A-3 embedding.
     *
     * @return bool
     */
    public function requiresPdfEmbedding(): bool
    {
        return match ($this) {
            self::FACTURX, self::ZUGFERD_10, self::ZUGFERD_20 => true,
            default                                           => false,
        };
    }

    /**
     * Get the XML namespace for this format.
     *
     * @return string
     */
    public function xmlNamespace(): string
    {
        return match ($this) {
            self::UBL_21, self::UBL_24, self::OIOUBL, self::EHF_30 => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            self::CII, self::FACTURX, self::ZUGFERD_20             => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
            self::ZUGFERD_10                                       => 'urn:ferd:CrossIndustryDocument:invoice:1p0',
            self::FACTURAE_32                                      => 'http://www.facturae.gob.es/formato/Versiones/Facturaev3_2.xml',
            self::FATTURAPA_12                                     => 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2',
            self::PEPPOL_BIS_30                                    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
        };
    }

    /**
     * Check if this format is mandatory for the given country.
     *
     * @param string|null $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return bool
     */
    public function isMandatoryFor(?string $countryCode): bool
    {
        $country = mb_strtoupper($countryCode ?? '');

        return match ($this) {
            self::FATTURAPA_12 => $country === 'IT',
            // Note: FACTURAE_32 is only mandatory for Spanish public administration
            // Not for all invoices in Spain, so we return false
            default => false,
        };
    }
}
