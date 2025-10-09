<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Enums;

use Modules\Invoices\Peppol\Enums\PeppolEndpointScheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolEndpointSchemeTest - Unit tests for PeppolEndpointScheme enum.
 *
 * Tests participant identifier schemes including country mappings,
 * validation logic, and formatting rules.
 *
 * @package Modules\Invoices\Tests\Unit\Peppol\Enums
 */
#[Group('peppol')]
class PeppolEndpointSchemeTest extends TestCase
{
    #[Test]
    public function it_has_all_expected_schemes(): void
    {
        $schemes = PeppolEndpointScheme::cases();

        $this->assertCount(17, $schemes);
        $this->assertContains(PeppolEndpointScheme::BE_CBE, $schemes);
        $this->assertContains(PeppolEndpointScheme::DE_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::FR_SIRENE, $schemes);
        $this->assertContains(PeppolEndpointScheme::IT_VAT, $schemes);
        $this->assertContains(PeppolEndpointScheme::GLN, $schemes);
        $this->assertContains(PeppolEndpointScheme::DUNS, $schemes);
    }

    #[Test]
    #[DataProvider('labelProvider')]
    public function it_provides_correct_labels(
        PeppolEndpointScheme $scheme,
        string $expectedLabel
    ): void {
        $this->assertEquals($expectedLabel, $scheme->label());
    }

    public static function labelProvider(): array
    {
        return [
            [PeppolEndpointScheme::BE_CBE, 'Belgian CBE/KBO/BCE Number'],
            [PeppolEndpointScheme::DE_VAT, 'German VAT Number'],
            [PeppolEndpointScheme::FR_SIRENE, 'French SIREN/SIRET'],
            [PeppolEndpointScheme::IT_VAT, 'Italian VAT Number (Partita IVA)'],
            [PeppolEndpointScheme::GLN, 'Global Location Number (GLN)'],
            [PeppolEndpointScheme::DUNS, 'DUNS Number'],
        ];
    }

    #[Test]
    #[DataProvider('descriptionProvider')]
    public function it_provides_descriptions(
        PeppolEndpointScheme $scheme,
        string $expectedDescription
    ): void {
        $description = $scheme->description();
        
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
        $this->assertStringContainsString($expectedDescription, $description);
    }

    public static function descriptionProvider(): array
    {
        return [
            [PeppolEndpointScheme::BE_CBE, '10 digits'],
            [PeppolEndpointScheme::DE_VAT, 'DE + 9 digits'],
            [PeppolEndpointScheme::FR_SIRENE, '9 or 14 digits'],
            [PeppolEndpointScheme::IT_VAT, 'IT + 11 digits'],
        ];
    }

    #[Test]
    #[DataProvider('countryMappingProvider')]
    public function it_maps_countries_to_schemes(
        string $countryCode,
        PeppolEndpointScheme $expectedScheme
    ): void {
        $scheme = PeppolEndpointScheme::forCountry($countryCode);

        $this->assertEquals($expectedScheme, $scheme);
    }

    public static function countryMappingProvider(): array
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
        ];
    }

    #[Test]
    public function it_defaults_to_iso_6523_for_unknown_countries(): void
    {
        $scheme = PeppolEndpointScheme::forCountry('XX');

        $this->assertEquals(PeppolEndpointScheme::ISO_6523, $scheme);
    }

    #[Test]
    public function it_handles_null_country_code(): void
    {
        $scheme = PeppolEndpointScheme::forCountry(null);

        $this->assertEquals(PeppolEndpointScheme::ISO_6523, $scheme);
    }

    #[Test]
    #[DataProvider('validIdentifierProvider')]
    public function it_validates_correct_identifiers(
        PeppolEndpointScheme $scheme,
        string $identifier,
        bool $expectedValid
    ): void {
        $isValid = $scheme->validates($identifier);

        $this->assertEquals($expectedValid, $isValid);
    }

    public static function validIdentifierProvider(): array
    {
        return [
            // Belgian CBE - 10 digits
            [PeppolEndpointScheme::BE_CBE, '0123456789', true],
            [PeppolEndpointScheme::BE_CBE, '012345678', false],
            [PeppolEndpointScheme::BE_CBE, '01234567890', false],
            
            // German VAT - DE + 9 digits
            [PeppolEndpointScheme::DE_VAT, 'DE123456789', true],
            [PeppolEndpointScheme::DE_VAT, 'DE12345678', false],
            [PeppolEndpointScheme::DE_VAT, '123456789', false],
            
            // French SIRENE - 9 or 14 digits
            [PeppolEndpointScheme::FR_SIRENE, '123456789', true],
            [PeppolEndpointScheme::FR_SIRENE, '12345678901234', true],
            [PeppolEndpointScheme::FR_SIRENE, '1234567890', false],
            
            // Italian VAT - IT + 11 digits
            [PeppolEndpointScheme::IT_VAT, 'IT12345678901', true],
            [PeppolEndpointScheme::IT_VAT, 'IT1234567890', false],
            [PeppolEndpointScheme::IT_VAT, '12345678901', false],
            
            // Dutch KVK - 8 digits
            [PeppolEndpointScheme::NL_KVK, '12345678', true],
            [PeppolEndpointScheme::NL_KVK, '1234567', false],
            
            // GLN - 13 digits
            [PeppolEndpointScheme::GLN, '1234567890123', true],
            [PeppolEndpointScheme::GLN, '123456789012', false],
            
            // DUNS - 9 digits
            [PeppolEndpointScheme::DUNS, '123456789', true],
            [PeppolEndpointScheme::DUNS, '12345678', false],
        ];
    }

    #[Test]
    #[DataProvider('formatProvider')]
    public function it_formats_identifiers_correctly(
        PeppolEndpointScheme $scheme,
        string $input,
        string $expectedOutput
    ): void {
        $formatted = $scheme->format($input);

        $this->assertEquals($expectedOutput, $formatted);
    }

    public static function formatProvider(): array
    {
        return [
            // Swedish - adds hyphen
            [PeppolEndpointScheme::SE_ORGNR, '1234567890', '123456-7890'],
            [PeppolEndpointScheme::SE_ORGNR, '123456-7890', '123456-7890'],
            
            // Finnish - adds hyphen
            [PeppolEndpointScheme::FI_OVT, '12345678', '1234567-8'],
            [PeppolEndpointScheme::FI_OVT, '1234567-8', '1234567-8'],
            
            // Others - no formatting
            [PeppolEndpointScheme::BE_CBE, '0123456789', '0123456789'],
            [PeppolEndpointScheme::DE_VAT, 'DE123456789', 'DE123456789'],
        ];
    }

    #[Test]
    public function it_validates_italian_codice_fiscale_format(): void
    {
        $scheme = PeppolEndpointScheme::IT_CF;

        // Valid 16-character alphanumeric
        $this->assertTrue($scheme->validates('RSSMRA80A01H501U'));
        $this->assertTrue($scheme->validates('ABCDEF12G34H567I'));
        
        // Invalid formats
        $this->assertFalse($scheme->validates('RSSMRA80A01H501')); // Too short
        $this->assertFalse($scheme->validates('RSSMRA80A01H501UX')); // Too long
        $this->assertFalse($scheme->validates('rssmra80a01h501u')); // Lowercase (after strtoupper)
    }

    #[Test]
    public function it_validates_spanish_nif_format(): void
    {
        $scheme = PeppolEndpointScheme::ES_VAT;

        // Valid formats: letter + 7-8 digits + letter/digit
        $this->assertTrue($scheme->validates('A12345678'));
        $this->assertTrue($scheme->validates('B1234567X'));
        
        // Invalid formats
        $this->assertFalse($scheme->validates('12345678A')); // Wrong position
        $this->assertFalse($scheme->validates('A123456')); // Too short
    }

    #[Test]
    public function it_validates_swiss_uid_with_flexible_separators(): void
    {
        $scheme = PeppolEndpointScheme::CH_UIDB;

        // Various formats with different separators
        $this->assertTrue($scheme->validates('CHE-123.456.789'));
        $this->assertTrue($scheme->validates('CHE-123-456-789'));
        $this->assertTrue($scheme->validates('CHE 123 456 789'));
        $this->assertTrue($scheme->validates('CHE123456789'));
        
        // Invalid
        $this->assertFalse($scheme->validates('CHE12345678')); // Wrong digit count
    }

    #[Test]
    public function it_validates_uk_companies_house_alphanumeric(): void
    {
        $scheme = PeppolEndpointScheme::GB_COH;

        $this->assertTrue($scheme->validates('12345678'));
        $this->assertTrue($scheme->validates('AB123456'));
        $this->assertTrue($scheme->validates('SC123456')); // Scottish company
        
        $this->assertFalse($scheme->validates('1234567')); // Too short
        $this->assertFalse($scheme->validates('123456789')); // Too long
    }

    #[Test]
    public function it_has_flexible_validation_for_iso_6523(): void
    {
        $scheme = PeppolEndpointScheme::ISO_6523;

        // Accept any non-empty string
        $this->assertTrue($scheme->validates('anything'));
        $this->assertTrue($scheme->validates('123'));
        $this->assertTrue($scheme->validates('abc-123'));
        
        $this->assertFalse($scheme->validates(''));
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
        $this->expectException(\ValueError::class);
        PeppolEndpointScheme::from('INVALID:SCHEME');
    }

    #[Test]
    public function it_handles_case_insensitive_country_codes(): void
    {
        $scheme1 = PeppolEndpointScheme::forCountry('BE');
        $scheme2 = PeppolEndpointScheme::forCountry('be');

        $this->assertEquals($scheme1, $scheme2);
        $this->assertEquals(PeppolEndpointScheme::BE_CBE, $scheme1);
    }
}