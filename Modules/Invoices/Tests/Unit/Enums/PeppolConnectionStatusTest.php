<?php

namespace Modules\Invoices\Tests\Unit\Enums;

use Modules\Invoices\Enums\PeppolConnectionStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolConnectionStatusTest - Unit tests for PeppolConnectionStatus enum.
 *
 * Tests the connection status enum including labels, colors, icons,
 * and instantiation from values.
 *
 * @package Modules\Invoices\Tests\Unit\Enums
 */
#[Group('peppol')]
class PeppolConnectionStatusTest extends TestCase
{
    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PeppolConnectionStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(PeppolConnectionStatus::UNTESTED, $cases);
        $this->assertContains(PeppolConnectionStatus::SUCCESS, $cases);
        $this->assertContains(PeppolConnectionStatus::FAILED, $cases);
    }

    #[Test]
    #[DataProvider('labelProvider')]
    public function it_provides_correct_labels(
        PeppolConnectionStatus $status,
        string $expectedLabel
    ): void {
        $this->assertEquals($expectedLabel, $status->label());
    }

    public static function labelProvider(): array
    {
        return [
            [PeppolConnectionStatus::UNTESTED, 'Untested'],
            [PeppolConnectionStatus::SUCCESS, 'Success'],
            [PeppolConnectionStatus::FAILED, 'Failed'],
        ];
    }

    #[Test]
    #[DataProvider('colorProvider')]
    public function it_provides_correct_colors(
        PeppolConnectionStatus $status,
        string $expectedColor
    ): void {
        $this->assertEquals($expectedColor, $status->color());
    }

    public static function colorProvider(): array
    {
        return [
            [PeppolConnectionStatus::UNTESTED, 'gray'],
            [PeppolConnectionStatus::SUCCESS, 'green'],
            [PeppolConnectionStatus::FAILED, 'red'],
        ];
    }

    #[Test]
    #[DataProvider('iconProvider')]
    public function it_provides_correct_icons(
        PeppolConnectionStatus $status,
        string $expectedIcon
    ): void {
        $this->assertEquals($expectedIcon, $status->icon());
    }

    public static function iconProvider(): array
    {
        return [
            [PeppolConnectionStatus::UNTESTED, 'heroicon-o-question-mark-circle'],
            [PeppolConnectionStatus::SUCCESS, 'heroicon-o-check-circle'],
            [PeppolConnectionStatus::FAILED, 'heroicon-o-x-circle'],
        ];
    }

    #[Test]
    #[DataProvider('valueProvider')]
    public function it_has_correct_enum_values(
        PeppolConnectionStatus $status,
        string $expectedValue
    ): void {
        $this->assertEquals($expectedValue, $status->value);
    }

    public static function valueProvider(): array
    {
        return [
            [PeppolConnectionStatus::UNTESTED, 'untested'],
            [PeppolConnectionStatus::SUCCESS, 'success'],
            [PeppolConnectionStatus::FAILED, 'failed'],
        ];
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $status = PeppolConnectionStatus::from('success');

        $this->assertEquals(PeppolConnectionStatus::SUCCESS, $status);
    }

    #[Test]
    public function it_throws_on_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        PeppolConnectionStatus::from('invalid_status');
    }

    #[Test]
    public function it_can_try_from_value_returning_null_on_invalid(): void
    {
        $status = PeppolConnectionStatus::tryFrom('invalid');

        $this->assertNull($status);
    }

    #[Test]
    public function it_can_be_used_in_match_expressions(): void
    {
        $status = PeppolConnectionStatus::SUCCESS;

        $message = match ($status) {
            PeppolConnectionStatus::UNTESTED => 'Not yet tested',
            PeppolConnectionStatus::SUCCESS => 'Connection successful',
            PeppolConnectionStatus::FAILED => 'Connection failed',
        };

        $this->assertEquals('Connection successful', $message);
    }

    #[Test]
    public function it_provides_all_cases_for_selection(): void
    {
        $cases = PeppolConnectionStatus::cases();
        $options = [];
        
        foreach ($cases as $case) {
            $options[$case->value] = $case->label();
        }

        $this->assertArrayHasKey('untested', $options);
        $this->assertArrayHasKey('success', $options);
        $this->assertArrayHasKey('failed', $options);
        $this->assertEquals('Untested', $options['untested']);
        $this->assertEquals('Success', $options['success']);
        $this->assertEquals('Failed', $options['failed']);
    }
}