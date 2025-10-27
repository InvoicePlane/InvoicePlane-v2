<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Enums;

use Modules\Invoices\Peppol\Enums\PeppolEndpointScheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use ValueError;

/**
 * PeppolEndpointSchemeTest - Unit tests for PeppolEndpointScheme enum.
 *
 * Tests the endpoint scheme enum including country-based recommendations
 * and identifier validation.
 */
#[Group('peppol')]
class PeppolEndpointSchemeTest extends TestCase
{
    public static function countrySchemeProvider(): array
    {
        return [
            ['BE', PeppolEndpointScheme::BE_CBE],
            ['DE', PeppolEndpointScheme::DE_VAT],
            ['FR', PeppolEndpointScheme::FR_SIRENE],
            ['IT', PeppolEndpointScheme::IT_VAT],
            ['ES', PeppolEndpointScheme::ES_VAT],
            ['NL', PeppolEndpointScheme::NL_KVK],
            ['NO', PeppolEndpointScheme::NO_ORGNR],
            ['DK', PeppolEndpointScheme::DK_CVR],
            ['SE', PeppolEndpointScheme::SE_ORGNR],
            ['FI', PeppolEndpointScheme::FI_OVT],
            ['AT', PeppolEndpointScheme::AT_VAT],
            ['CH', PeppolEndpointScheme::CH_UIDB],
            ['GB', PeppolEndpointScheme::GB_COH],
            ['XX', PeppolEndpointScheme::ISO_6523], // Unknown country
        ];
    }

    public static function identifierValidationProvider(): array
    {
        return [
            // Belgian CBE - 10 digits
            [PeppolEndpointScheme::BE_CBE, '0123456789', true],
            [PeppolEndpointScheme::BE_CBE, '012345678', false], // Too short
            [PeppolEndpointScheme::BE_CBE, '01234567890', false], // Too long

            // German VAT - DE + 9 digits
            [PeppolEndpointScheme::DE_VAT, 'DE123456789', true],
            [PeppolEndpointScheme::DE_VAT, 'DE12345678', false], // Too short
            [PeppolEndpointScheme::DE_VAT, '123456789', false], // Missing DE prefix

            // French SIRENE - 9 or 14 digits
            [PeppolEndpointScheme::FR_SIRENE, '123456789', true],
            [PeppolEndpointScheme::FR_SIRENE, '12345678912345', true],
            [PeppolEndpointScheme::FR_SIRENE, '12345678', false], // Too short

            // Italian VAT - IT + 11 digits
            [PeppolEndpointScheme::IT_VAT, 'IT12345678901', true],
            [PeppolEndpointScheme::IT_VAT, 'IT1234567890', false], // Too short
            [PeppolEndpointScheme::IT_VAT, '12345678901', false], // Missing IT prefix

            // Spanish NIF/CIF - Letter + 7-8 digits + letter/digit
            [PeppolEndpointScheme::ES_VAT, 'A12345678', true],
            [PeppolEndpointScheme::ES_VAT, 'B1234567C', true],
            [PeppolEndpointScheme::ES_VAT, '12345678A', false], // Wrong format

            // Dutch KVK - 8 digits
            [PeppolEndpointScheme::NL_KVK, '12345678', true],
            [PeppolEndpointScheme::NL_KVK, '1234567', false], // Too short

            // Norwegian Organization Number - 9 digits
            [PeppolEndpointScheme::NO_ORGNR, '123456789', true],
            [PeppolEndpointScheme::NO_ORGNR, '12345678', false], // Too short

            // Danish CVR - 8 digits
            [PeppolEndpointScheme::DK_CVR, '12345678', true],
            [PeppolEndpointScheme::DK_CVR, '1234567', false], // Too short

            // Swedish Organization Number - 10 digits (with or without hyphen)
            [PeppolEndpointScheme::SE_ORGNR, '123456-7890', true],
            [PeppolEndpointScheme::SE_ORGNR, '1234567890', true],
            [PeppolEndpointScheme::SE_ORGNR, '12345-6789', false], // Wrong format

            // Finnish Business ID - 7 digits + check digit (with or without hyphen)
            [PeppolEndpointScheme::FI_OVT, '1234567-8', true],
            [PeppolEndpointScheme::FI_OVT, '12345678', true],
            [PeppolEndpointScheme::FI_OVT, '123456-78', false], // Wrong format

            // GLN - 13 digits
            [PeppolEndpointScheme::GLN, '1234567890123', true],
            [PeppolEndpointScheme::GLN, '123456789012', false], // Too short

            // DUNS - 9 digits
            [PeppolEndpointScheme::DUNS, '123456789', true],
            [PeppolEndpointScheme::DUNS, '12345678', false], // Too short

            // ISO 6523 - Flexible
            [PeppolEndpointScheme::ISO_6523, 'any-value', true],
            [PeppolEndpointScheme::ISO_6523, '', false], // Empty
        ];
    }

    public static function formatIdentifierProvider(): array
    {
        return [
            // Swedish Organization Number - adds hyphen
            [PeppolEndpointScheme::SE_ORGNR, '1234567890', '123456-7890'],
            [PeppolEndpointScheme::SE_ORGNR, '123456-7890', '123456-7890'], // Already formatted

            // Finnish Business ID - adds hyphen
            [PeppolEndpointScheme::FI_OVT, '12345678', '1234567-8'],
            [PeppolEndpointScheme::FI_OVT, '1234567-8', '1234567-8'], // Already formatted

            // Others remain unchanged
            [PeppolEndpointScheme::BE_CBE, '0123456789', '0123456789'],
            [PeppolEndpointScheme::DE_VAT, 'DE123456789', 'DE123456789'],
        ];
    }

    #[Test]
    public function it_has_all_expected_schemes(): void
    {
        $schemes = PeppolEndpointScheme::cases();

        $this->assertCount(17, $schemes);
        $this->assertContains(PeppolEndpointScheme::BE_CBE, $schemes);
        $this->assertContains(PeppolEndpointScheme::DE_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::FR_SIRENE, $schemes);
        $this->assertContains(PeppolEndpointScheme::IT_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::IT_CF, $schemes);
        $this->assertContains(PeppolEndpointScheme::ES_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::NL_KVK, $schemes);
        $this->assertContains(PeppolEndpointScheme::NO_ORGNR, $schemes);
        $this->assertContains(PeppolEndpointScheme::DK_CVR, $schemes);
        $this->assertContains(PeppolEndpointScheme::SE_ORGNR, $schemes);
        $this->assertContains(PeppolEndpointScheme::FI_OVT, $schemes);
        $this->assertContains(PeppolEndpointScheme::AT_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::CH_UIDB, $schemes);
        $this->assertContains(PeppolEndpointScheme::GB_COH, $schemes);
        $this->assertContains(PeppolEndpointScheme::GLN, $schemes);
        $this->assertContains(PeppolEndpointScheme::DUNS, $schemes);
        $this->assertContains(PeppolEndpointScheme::ISO_6523, $schemes);
    }

    #[Test]
    #[DataProvider('countrySchemeProvider')]
    public function it_returns_correct_scheme_for_country(
        string $countryCode,
        PeppolEndpointScheme $expectedScheme
    ): void {
        $scheme = PeppolEndpointScheme::forCountry($countryCode);

        $this->assertEquals($expectedScheme, $scheme);
    }

    #[Test]
    #[DataProvider('identifierValidationProvider')]
    public function it_validates_identifiers_correctly(
        PeppolEndpointScheme $scheme,
        string $identifier,
        bool $expectedValid
    ): void {
        $isValid = $scheme->validates($identifier);

        $this->assertEquals($expectedValid, $isValid);
    }

    #[Test]
    public function it_provides_label_for_schemes(): void
    {
        $this->assertEquals('Belgian CBE/KBO/BCE Number', PeppolEndpointScheme::BE_CBE->label());
        $this->assertEquals('German VAT Number', PeppolEndpointScheme::DE_VAT->label());
        $this->assertEquals('French SIREN/SIRET', PeppolEndpointScheme::FR_SIRENE->label());
        $this->assertEquals('Italian VAT Number (Partita IVA)', PeppolEndpointScheme::IT_VAT->label());
        $this->assertEquals('Global Location Number (GLN)', PeppolEndpointScheme::GLN->label());
    }

    #[Test]
    public function it_provides_description_for_schemes(): void
    {
        $description = PeppolEndpointScheme::BE_CBE->description();

        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }

    #[Test]
    #[DataProvider('formatIdentifierProvider')]
    public function it_formats_identifiers_correctly(
        PeppolEndpointScheme $scheme,
        string $rawIdentifier,
        string $expectedFormatted
    ): void {
        $formatted = $scheme->format($rawIdentifier);

        $this->assertEquals($expectedFormatted, $formatted);
    }

    #[Test]
    public function it_handles_null_country_code_gracefully(): void
    {
        $scheme = PeppolEndpointScheme::forCountry(null);

        $this->assertEquals(PeppolEndpointScheme::ISO_6523, $scheme);
    }

    #[Test]
    public function it_handles_lowercase_country_codes(): void
    {
        $scheme = PeppolEndpointScheme::forCountry('it');

        $this->assertEquals(PeppolEndpointScheme::IT_VAT, $scheme);
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $scheme = PeppolEndpointScheme::from('BE:CBE');

        $this->assertEquals(PeppolEndpointScheme::BE_CBE, $scheme);
    }

    #[Test]
    public function it_throws_on_invalid_value(): void
    {
        $this->expectException(ValueError::class);
        PeppolEndpointScheme::from('invalid_scheme');
    }
}
