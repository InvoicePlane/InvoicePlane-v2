<?php

namespace Modules\Invoices\Tests\Unit\Enums;

use Modules\Core\Tests\TestCase;
use Modules\Invoices\Enums\PeppolErrorType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ValueError;

/**
 * PeppolErrorTypeTest - Unit tests for PeppolErrorType enum.
 *
 * Tests error type classification including labels, colors, and icons.
 */
#[Group('peppol')]
class PeppolErrorTypeTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            [PeppolErrorType::TRANSIENT, 'Transient Error'],
            [PeppolErrorType::PERMANENT, 'Permanent Error'],
            [PeppolErrorType::UNKNOWN, 'Unknown Error'],
        ];
    }

    public static function colorProvider(): array
    {
        return [
            [PeppolErrorType::TRANSIENT, 'yellow'],
            [PeppolErrorType::PERMANENT, 'red'],
            [PeppolErrorType::UNKNOWN, 'gray'],
        ];
    }

    public static function iconProvider(): array
    {
        return [
            [PeppolErrorType::TRANSIENT, 'heroicon-o-arrow-path'],
            [PeppolErrorType::PERMANENT, 'heroicon-o-x-circle'],
            [PeppolErrorType::UNKNOWN, 'heroicon-o-question-mark-circle'],
        ];
    }

    public static function valueProvider(): array
    {
        return [
            [PeppolErrorType::TRANSIENT, 'TRANSIENT'],
            [PeppolErrorType::PERMANENT, 'PERMANENT'],
            [PeppolErrorType::UNKNOWN, 'UNKNOWN'],
        ];
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PeppolErrorType::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(PeppolErrorType::TRANSIENT, $cases);
        $this->assertContains(PeppolErrorType::PERMANENT, $cases);
        $this->assertContains(PeppolErrorType::UNKNOWN, $cases);
    }

    #[Test]
    #[DataProvider('labelProvider')]
    public function it_provides_correct_labels(
        PeppolErrorType $type,
        string $expectedLabel
    ): void {
        $this->assertEquals($expectedLabel, $type->label());
    }

    #[Test]
    #[DataProvider('colorProvider')]
    public function it_provides_correct_colors(
        PeppolErrorType $type,
        string $expectedColor
    ): void {
        $this->assertEquals($expectedColor, $type->color());
    }

    #[Test]
    #[DataProvider('iconProvider')]
    public function it_provides_correct_icons(
        PeppolErrorType $type,
        string $expectedIcon
    ): void {
        $this->assertEquals($expectedIcon, $type->icon());
    }

    #[Test]
    #[DataProvider('valueProvider')]
    public function it_has_correct_enum_values(
        PeppolErrorType $type,
        string $expectedValue
    ): void {
        $this->assertEquals($expectedValue, $type->value);
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $type = PeppolErrorType::from('TRANSIENT');

        $this->assertEquals(PeppolErrorType::TRANSIENT, $type);
    }

    #[Test]
    public function it_throws_on_invalid_value(): void
    {
        $this->expectException(ValueError::class);
        PeppolErrorType::from('INVALID');
    }

    #[Test]
    public function it_distinguishes_retryable_vs_permanent_errors(): void
    {
        $transient = PeppolErrorType::TRANSIENT;
        $permanent = PeppolErrorType::PERMANENT;

        // Transient errors typically warrant retry
        $this->assertEquals('yellow', $transient->color());
        $this->assertStringContainsString('arrow-path', $transient->icon());

        // Permanent errors should not be retried
        $this->assertEquals('red', $permanent->color());
        $this->assertStringContainsString('x-circle', $permanent->icon());
    }
}
