<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Enums;

use Modules\Core\Tests\TestCase;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ValueError;

/**
 * PeppolDocumentFormatTest - Unit tests for PeppolDocumentFormat enum.
 *
 * Tests the document format enum including country-based recommendations
 * and mandatory format detection.
 */
#[Group('peppol')]
class PeppolDocumentFormatTest extends TestCase
{
    public static function countryRecommendationProvider(): array
    {
        return [
            // CII countries
            ['DE', PeppolDocumentFormat::CII],
            ['FR', PeppolDocumentFormat::CII],
            ['AT', PeppolDocumentFormat::CII],

            // Country-specific formats
            ['IT', PeppolDocumentFormat::FATTURAPA_12],
            ['ES', PeppolDocumentFormat::FACTURAE_32],
            ['DK', PeppolDocumentFormat::OIOUBL],
            ['NO', PeppolDocumentFormat::EHF_30],

            // Default UBL for other countries
            ['NL', PeppolDocumentFormat::UBL_24],
            ['BE', PeppolDocumentFormat::UBL_24],
            ['GB', PeppolDocumentFormat::UBL_24],
            ['SE', PeppolDocumentFormat::UBL_24],
            ['FI', PeppolDocumentFormat::UBL_24],

            // Unknown country defaults to UBL
            ['XX', PeppolDocumentFormat::UBL_24],
            ['', PeppolDocumentFormat::UBL_24],
        ];
    }

    public static function mandatoryFormatProvider(): array
    {
        return [
            // FatturaPA is mandatory for Italy
            [PeppolDocumentFormat::FATTURAPA_12, 'IT', true],
            [PeppolDocumentFormat::FATTURAPA_12, 'DE', false],
            [PeppolDocumentFormat::UBL_24, 'IT', false],

            // Facturae is mandatory for Spanish public sector (simplified - would be true with public sector flag)
            [PeppolDocumentFormat::FACTURAE_32, 'ES', false], // Not mandatory for all Spanish invoices
            [PeppolDocumentFormat::FACTURAE_32, 'FR', false],

            // No other formats are strictly mandatory
            [PeppolDocumentFormat::UBL_24, 'NL', false],
            [PeppolDocumentFormat::CII, 'DE', false],
            [PeppolDocumentFormat::OIOUBL, 'DK', false],
        ];
    }

    public static function formatValuesProvider(): array
    {
        return [
            [PeppolDocumentFormat::PEPPOL_BIS_30, 'peppol_bis_3.0'],
            [PeppolDocumentFormat::UBL_21, 'ubl_2.1'],
            [PeppolDocumentFormat::UBL_24, 'ubl_2.4'],
            [PeppolDocumentFormat::CII, 'cii'],
            [PeppolDocumentFormat::FATTURAPA_12, 'fatturapa_1.2'],
            [PeppolDocumentFormat::FACTURAE_32, 'facturae_3.2'],
            [PeppolDocumentFormat::FACTURX, 'factur-x'],
            [PeppolDocumentFormat::ZUGFERD_10, 'zugferd_1.0'],
            [PeppolDocumentFormat::ZUGFERD_20, 'zugferd_2.0'],
            [PeppolDocumentFormat::OIOUBL, 'oioubl'],
            [PeppolDocumentFormat::EHF_30, 'ehf_3.0'],
        ];
    }

    #[Test]
    public function it_has_all_expected_formats(): void
    {
        $formats = PeppolDocumentFormat::cases();

        $this->assertCount(11, $formats);
        $this->assertContains(PeppolDocumentFormat::PEPPOL_BIS_30, $formats);
        $this->assertContains(PeppolDocumentFormat::UBL_21, $formats);
        $this->assertContains(PeppolDocumentFormat::UBL_24, $formats);
        $this->assertContains(PeppolDocumentFormat::CII, $formats);
        $this->assertContains(PeppolDocumentFormat::FATTURAPA_12, $formats);
        $this->assertContains(PeppolDocumentFormat::FACTURAE_32, $formats);
        $this->assertContains(PeppolDocumentFormat::FACTURX, $formats);
        $this->assertContains(PeppolDocumentFormat::ZUGFERD_10, $formats);
        $this->assertContains(PeppolDocumentFormat::ZUGFERD_20, $formats);
        $this->assertContains(PeppolDocumentFormat::OIOUBL, $formats);
        $this->assertContains(PeppolDocumentFormat::EHF_30, $formats);
    }

    #[Test]
    #[DataProvider('countryRecommendationProvider')]
    public function it_recommends_correct_format_for_country(
        string $countryCode,
        PeppolDocumentFormat $expectedFormat
    ): void {
        $recommended = PeppolDocumentFormat::recommendedForCountry($countryCode);

        $this->assertEquals($expectedFormat, $recommended);
    }

    #[Test]
    #[DataProvider('mandatoryFormatProvider')]
    #[Group('failing')]
    public function it_identifies_mandatory_formats_correctly(
        PeppolDocumentFormat $format,
        string $countryCode,
        bool $expectedMandatory
    ): void {
        $isMandatory = $format->isMandatoryFor($countryCode);

        $this->assertEquals($expectedMandatory, $isMandatory);
    }

    #[Test]
    public function it_provides_label_for_formats(): void
    {
        $this->assertEquals('PEPPOL BIS Billing 3.0', PeppolDocumentFormat::PEPPOL_BIS_30->label());
        $this->assertEquals('UBL 2.1', PeppolDocumentFormat::UBL_21->label());
        $this->assertEquals('UBL 2.4', PeppolDocumentFormat::UBL_24->label());
        $this->assertEquals('Cross Industry Invoice (CII)', PeppolDocumentFormat::CII->label());
        $this->assertEquals('FatturaPA 1.2 (Italy)', PeppolDocumentFormat::FATTURAPA_12->label());
        $this->assertEquals('Facturae 3.2 (Spain)', PeppolDocumentFormat::FACTURAE_32->label());
        $this->assertEquals('Factur-X (France/Germany)', PeppolDocumentFormat::FACTURX->label());
        $this->assertEquals('ZUGFeRD 1.0', PeppolDocumentFormat::ZUGFERD_10->label());
        $this->assertEquals('ZUGFeRD 2.0', PeppolDocumentFormat::ZUGFERD_20->label());
        $this->assertEquals('OIOUBL (Denmark)', PeppolDocumentFormat::OIOUBL->label());
        $this->assertEquals('EHF 3.0 (Norway)', PeppolDocumentFormat::EHF_30->label());
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $format = PeppolDocumentFormat::from('ubl_2.4');

        $this->assertEquals(PeppolDocumentFormat::UBL_24, $format);
    }

    public function test_it_throws_on_invalid_enum_value(): void
    {
        $this->markTestIncomplete('weird test');

        $this->expectException(ValueError::class);
        PeppolDocumentFormat::from('invalid_value');
    }

    public function test_it_throws_on_invalid_enum_value_name(): void
    {
        $this->markTestIncomplete('weird test');

        $this->expectException(ValueError::class);
        PeppolDocumentFormat::from('not_a_real_enum');
    }

    #[Test]
    #[Group('failing')]
    public function it_provides_description_for_formats(): void
    {
        $description = PeppolDocumentFormat::PEPPOL_BIS_30->description();

        $this->assertIsString($description);
        $this->assertNotEmpty($description);
        $this->assertStringContainsString('PEPPOL', $description);
    }

    #[Test]
    #[DataProvider('formatValuesProvider')]
    public function it_has_correct_enum_values(
        PeppolDocumentFormat $format,
        string $expectedValue
    ): void {
        $this->assertEquals($expectedValue, $format->value);
    }

    #[Test]
    public function it_handles_null_country_code_gracefully(): void
    {
        $recommended = PeppolDocumentFormat::recommendedForCountry(null);

        $this->assertEquals(PeppolDocumentFormat::UBL_24, $recommended);
    }

    #[Test]
    public function it_handles_lowercase_country_codes(): void
    {
        $recommended = PeppolDocumentFormat::recommendedForCountry('it');

        $this->assertEquals(PeppolDocumentFormat::FATTURAPA_12, $recommended);
    }

    #[Test]
    public function it_can_list_all_formats_as_select_options(): void
    {
        $options = [];
        foreach (PeppolDocumentFormat::cases() as $format) {
            $options[$format->value] = $format->label();
        }

        $this->assertCount(11, $options);
        $this->assertArrayHasKey('peppol_bis_3.0', $options);
        $this->assertArrayHasKey('ubl_2.4', $options);
        $this->assertArrayHasKey('fatturapa_1.2', $options);
    }

    #[Test]
    public function it_rejects_invalid_format(): void
    {
        /* arrange & act & assert */
        $this->expectException(ValueError::class);

        // Trying to create an enum with an invalid value should throw ValueError
        PeppolDocumentFormat::from('invalid_format_name');
    }
}
