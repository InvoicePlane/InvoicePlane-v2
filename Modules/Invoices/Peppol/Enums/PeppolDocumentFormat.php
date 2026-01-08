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
    case FACTURX_10 = 'facturx_1.0';

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
    case EHF = 'ehf';

    /**
     * PEPPOL BIS Billing 3.0 - Pan-European standard.
     * Based on UBL 2.1 with Peppol-specific requirements.
     */
    case PEPPOL_BIS_30 = 'peppol_bis_3.0';

    /**
     * EHF 3.0 - Norwegian e-invoice format (specific version for Peppol tests).
     * Used in some test cases and country recommendations.
     */
    case EHF_30 = 'ehf_3.0';

    /**
     * Factur-X - Hybrid PDF/XML format (specific version for Peppol tests).
     * Used in some test cases and country recommendations.
     */
    case FACTURX = 'facturx';

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
            'ES'    => self::FACTURAE_32,
            'IT'    => self::FATTURAPA_12,
            'FR'    => self::FACTURX_10,
            'DE'    => self::ZUGFERD_20,
            'AT'    => self::CII,
            'DK'    => self::OIOUBL,
            'NO'    => self::EHF,
            default => self::PEPPOL_BIS_30,
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
            'ES'    => [self::FACTURAE_32, self::UBL_21, self::PEPPOL_BIS_30],
            'IT'    => [self::FATTURAPA_12, self::UBL_21, self::PEPPOL_BIS_30],
            'FR'    => [self::FACTURX_10, self::FACTURX, self::CII, self::UBL_21, self::PEPPOL_BIS_30],
            'DE'    => [self::ZUGFERD_20, self::ZUGFERD_10, self::CII, self::UBL_21, self::PEPPOL_BIS_30],
            'AT'    => [self::CII, self::UBL_21, self::PEPPOL_BIS_30],
            'DK'    => [self::OIOUBL, self::UBL_21, self::PEPPOL_BIS_30],
            'NO'    => [self::EHF_30, self::EHF, self::UBL_21, self::PEPPOL_BIS_30],
            default => [self::PEPPOL_BIS_30, self::UBL_21, self::CII],
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
            self::UBL_21        => 'UBL 2.1 (Universal Business Language)',
            self::UBL_24        => 'UBL 2.4 (Universal Business Language)',
            self::CII           => 'CII (Cross Industry Invoice)',
            self::FACTURAE_32   => 'Facturae 3.2 (Spain)',
            self::FATTURAPA_12  => 'FatturaPA 1.2 (Italy)',
            self::FACTURX_10    => 'Factur-X 1.0 (France/Germany)',
            self::ZUGFERD_10    => 'ZUGFeRD 1.0 (Germany)',
            self::ZUGFERD_20    => 'ZUGFeRD 2.0 (Germany)',
            self::OIOUBL        => 'OIOUBL (Denmark)',
            self::EHF           => 'EHF (Norway)',
            self::PEPPOL_BIS_30 => 'PEPPOL BIS Billing 3.0',
            self::EHF_30        => 'EHF 3.0 (Norway)',
            self::FACTURX       => 'Factur-X (France/Germany)',
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
            self::FACTURX_10    => 'Hybrid PDF/A-3 format with embedded XML. Used in France and Germany.',
            self::ZUGFERD_10    => 'German standard combining PDF with embedded XML invoice data.',
            self::ZUGFERD_20    => 'Updated ZUGFeRD compatible with Factur-X. Uses CII format.',
            self::OIOUBL        => 'Danish UBL-based format with national extensions.',
            self::EHF           => 'Norwegian UBL-based format used in public procurement.',
            self::PEPPOL_BIS_30 => 'Pan-European Public Procurement Online standard.',
            self::EHF_30        => 'Norwegian EHF 3.0 format for Peppol network.',
            self::FACTURX       => 'Hybrid PDF/A-3 format with embedded XML. Used in France and Germany.',
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
            self::FACTURX_10, self::ZUGFERD_10, self::ZUGFERD_20 => 'pdf',
            default => 'xml',
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
            self::FACTURX_10, self::ZUGFERD_10, self::ZUGFERD_20 => true,
            default => false,
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
            self::UBL_21, self::UBL_24, self::PEPPOL_BIS_30, self::OIOUBL, self::EHF => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            self::CII, self::FACTURX_10, self::ZUGFERD_20 => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
            self::ZUGFERD_10   => 'urn:ferd:CrossIndustryDocument:invoice:1p0',
            self::FACTURAE_32  => 'http://www.facturae.gob.es/formato/Versiones/Facturaev3_2.xml',
            self::FATTURAPA_12 => 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2',
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
            self::FACTURAE_32  => $country === 'ES', // For public administration
            default            => false,
        };
    }
}
