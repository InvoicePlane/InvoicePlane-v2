<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Enums;

use Modules\Invoices\Enums\PeppolTransmissionStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolTransmissionStatusTest - Unit tests for PeppolTransmissionStatus enum.
 *
 * Tests enum values, labels, colors, icons, and helper methods.
 *
 * @package Modules\Invoices\Tests\Unit\Peppol\Enums
 */
class PeppolTransmissionStatusTest extends TestCase
{
    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PeppolTransmissionStatus::cases();
        
        $this->assertCount(9, $cases);
        $this->assertContains(PeppolTransmissionStatus::PENDING, $cases);
        $this->assertContains(PeppolTransmissionStatus::QUEUED, $cases);
        $this->assertContains(PeppolTransmissionStatus::PROCESSING, $cases);
        $this->assertContains(PeppolTransmissionStatus::SENT, $cases);
        $this->assertContains(PeppolTransmissionStatus::ACCEPTED, $cases);
        $this->assertContains(PeppolTransmissionStatus::REJECTED, $cases);
        $this->assertContains(PeppolTransmissionStatus::FAILED, $cases);
        $this->assertContains(PeppolTransmissionStatus::RETRYING, $cases);
        $this->assertContains(PeppolTransmissionStatus::DEAD, $cases);
    }

    #[Test]
    public function it_returns_correct_labels(): void
    {
        $this->assertEquals('Pending', PeppolTransmissionStatus::PENDING->label());
        $this->assertEquals('Queued', PeppolTransmissionStatus::QUEUED->label());
        $this->assertEquals('Processing', PeppolTransmissionStatus::PROCESSING->label());
        $this->assertEquals('Sent', PeppolTransmissionStatus::SENT->label());
        $this->assertEquals('Accepted', PeppolTransmissionStatus::ACCEPTED->label());
        $this->assertEquals('Rejected', PeppolTransmissionStatus::REJECTED->label());
        $this->assertEquals('Failed', PeppolTransmissionStatus::FAILED->label());
        $this->assertEquals('Retrying', PeppolTransmissionStatus::RETRYING->label());
        $this->assertEquals('Dead', PeppolTransmissionStatus::DEAD->label());
    }

    #[Test]
    public function it_returns_correct_colors(): void
    {
        $this->assertEquals('gray', PeppolTransmissionStatus::PENDING->color());
        $this->assertEquals('blue', PeppolTransmissionStatus::QUEUED->color());
        $this->assertEquals('yellow', PeppolTransmissionStatus::PROCESSING->color());
        $this->assertEquals('indigo', PeppolTransmissionStatus::SENT->color());
        $this->assertEquals('green', PeppolTransmissionStatus::ACCEPTED->color());
        $this->assertEquals('red', PeppolTransmissionStatus::REJECTED->color());
        $this->assertEquals('orange', PeppolTransmissionStatus::FAILED->color());
        $this->assertEquals('purple', PeppolTransmissionStatus::RETRYING->color());
        $this->assertEquals('red', PeppolTransmissionStatus::DEAD->color());
    }

    #[Test]
    public function it_returns_correct_icons(): void
    {
        $this->assertEquals('heroicon-o-clock', PeppolTransmissionStatus::PENDING->icon());
        $this->assertEquals('heroicon-o-queue-list', PeppolTransmissionStatus::QUEUED->icon());
        $this->assertEquals('heroicon-o-arrow-path', PeppolTransmissionStatus::PROCESSING->icon());
        $this->assertEquals('heroicon-o-paper-airplane', PeppolTransmissionStatus::SENT->icon());
        $this->assertEquals('heroicon-o-check-circle', PeppolTransmissionStatus::ACCEPTED->icon());
        $this->assertEquals('heroicon-o-x-circle', PeppolTransmissionStatus::REJECTED->icon());
        $this->assertEquals('heroicon-o-exclamation-triangle', PeppolTransmissionStatus::FAILED->icon());
        $this->assertEquals('heroicon-o-arrow-path', PeppolTransmissionStatus::RETRYING->icon());
        $this->assertEquals('heroicon-o-no-symbol', PeppolTransmissionStatus::DEAD->icon());
    }

    #[Test]
    public function it_can_be_created_from_string_value(): void
    {
        $status = PeppolTransmissionStatus::from('pending');
        $this->assertEquals(PeppolTransmissionStatus::PENDING, $status);

        $status = PeppolTransmissionStatus::from('sent');
        $this->assertEquals(PeppolTransmissionStatus::SENT, $status);
    }

    #[Test]
    public function it_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        PeppolTransmissionStatus::from('invalid_status');
    }

    #[Test]
    public function it_can_use_try_from_for_safe_instantiation(): void
    {
        $status = PeppolTransmissionStatus::tryFrom('pending');
        $this->assertInstanceOf(PeppolTransmissionStatus::class, $status);

        $status = PeppolTransmissionStatus::tryFrom('invalid');
        $this->assertNull($status);
    }

    #[Test]
    public function it_returns_correct_string_values(): void
    {
        $this->assertEquals('pending', PeppolTransmissionStatus::PENDING->value);
        $this->assertEquals('sent', PeppolTransmissionStatus::SENT->value);
        $this->assertEquals('accepted', PeppolTransmissionStatus::ACCEPTED->value);
        $this->assertEquals('dead', PeppolTransmissionStatus::DEAD->value);
    }

    #[Test]
    public function it_can_be_compared(): void
    {
        $status1 = PeppolTransmissionStatus::PENDING;
        $status2 = PeppolTransmissionStatus::PENDING;
        $status3 = PeppolTransmissionStatus::SENT;

        $this->assertTrue($status1 === $status2);
        $this->assertFalse($status1 === $status3);
    }

    #[Test]
    public function it_provides_options_array(): void
    {
        $options = PeppolTransmissionStatus::options();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('pending', $options);
        $this->assertArrayHasKey('sent', $options);
        $this->assertEquals('Pending', $options['pending']);
        $this->assertEquals('Sent', $options['sent']);
    }
}