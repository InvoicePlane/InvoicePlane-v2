<?php

namespace Modules\Invoices\Tests\Unit\Enums;

use Modules\Invoices\Enums\PeppolValidationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolValidationStatusTest - Unit tests for PeppolValidationStatus enum.
 *
 * Tests Peppol ID validation status including labels, colors, and icons.
 *
 * @package Modules\Invoices\Tests\Unit\Enums
 */
#[Group('peppol')]
class PeppolValidationStatusTest extends TestCase
{
    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PeppolValidationStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(PeppolValidationStatus::VALID, $cases);
        $this->assertContains(PeppolValidationStatus::INVALID, $cases);
        $this->assertContains(PeppolValidationStatus::NOT_FOUND, $cases);
        $this->assertContains(PeppolValidationStatus::ERROR, $cases);
    }

    #[Test]
    #[DataProvider('labelProvider')]
    public function it_provides_correct_labels(
        PeppolValidationStatus $status,
        string $expectedLabel
    ): void {
        $this->assertEquals($expectedLabel, $status->label());
    }

    public static function labelProvider(): array
    {
        return [
            [PeppolValidationStatus::VALID, 'Valid'],
            [PeppolValidationStatus::INVALID, 'Invalid'],
            [PeppolValidationStatus::NOT_FOUND, 'Not Found'],
            [PeppolValidationStatus::ERROR, 'Error'],
        ];
    }

    #[Test]
    #[DataProvider('colorProvider')]
    public function it_provides_correct_colors(
        PeppolValidationStatus $status,
        string $expectedColor
    ): void {
        $this->assertEquals($expectedColor, $status->color());
    }

    public static function colorProvider(): array
    {
        return [
            [PeppolValidationStatus::VALID, 'green'],
            [PeppolValidationStatus::INVALID, 'red'],
            [PeppolValidationStatus::NOT_FOUND, 'orange'],
            [PeppolValidationStatus::ERROR, 'red'],
        ];
    }

    #[Test]
    #[DataProvider('iconProvider')]
    public function it_provides_correct_icons(
        PeppolValidationStatus $status,
        string $expectedIcon
    ): void {
        $this->assertEquals($expectedIcon, $status->icon());
    }

    public static function iconProvider(): array
    {
        return [
            [PeppolValidationStatus::VALID, 'heroicon-o-check-circle'],
            [PeppolValidationStatus::INVALID, 'heroicon-o-x-circle'],
            [PeppolValidationStatus::NOT_FOUND, 'heroicon-o-question-mark-circle'],
            [PeppolValidationStatus::ERROR, 'heroicon-o-exclamation-triangle'],
        ];
    }

    #[Test]
    #[DataProvider('valueProvider')]
    public function it_has_correct_enum_values(
        PeppolValidationStatus $status,
        string $expectedValue
    ): void {
        $this->assertEquals($expectedValue, $status->value);
    }

    public static function valueProvider(): array
    {
        return [
            [PeppolValidationStatus::VALID, 'valid'],
            [PeppolValidationStatus::INVALID, 'invalid'],
            [PeppolValidationStatus::NOT_FOUND, 'not_found'],
            [PeppolValidationStatus::ERROR, 'error'],
        ];
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $status = PeppolValidationStatus::from('valid');

        $this->assertEquals(PeppolValidationStatus::VALID, $status);
    }

    #[Test]
    public function it_throws_on_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        PeppolValidationStatus::from('unknown');
    }

    #[Test]
    public function it_distinguishes_success_from_error_states(): void
    {
        $valid = PeppolValidationStatus::VALID;
        $this->assertEquals('green', $valid->color());

        $invalid = PeppolValidationStatus::INVALID;
        $this->assertEquals('red', $invalid->color());

        $notFound = PeppolValidationStatus::NOT_FOUND;
        $this->assertEquals('orange', $notFound->color());

        $error = PeppolValidationStatus::ERROR;
        $this->assertEquals('red', $error->color());
    }

    #[Test]
    public function it_provides_appropriate_visual_indicators(): void
    {
        $valid = PeppolValidationStatus::VALID;
        $this->assertStringContainsString('check-circle', $valid->icon());

        $invalid = PeppolValidationStatus::INVALID;
        $this->assertStringContainsString('x-circle', $invalid->icon());
        
        $notFound = PeppolValidationStatus::NOT_FOUND;
        $this->assertStringContainsString('question-mark-circle', $notFound->icon());

        $error = PeppolValidationStatus::ERROR;
        $this->assertStringContainsString('exclamation-triangle', $error->icon());
    }
}