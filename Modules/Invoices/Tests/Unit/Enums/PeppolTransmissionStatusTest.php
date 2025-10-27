<?php

namespace Modules\Invoices\Tests\Unit\Enums;

use Modules\Core\Tests\TestCase;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ValueError;

/**
 * PeppolTransmissionStatusTest - Unit tests for PeppolTransmissionStatus enum.
 *
 * Tests transmission lifecycle status including state methods,
 * labels, colors, icons, and business logic.
 */
#[Group('peppol')]
class PeppolTransmissionStatusTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, 'Pending'],
            [PeppolTransmissionStatus::QUEUED, 'Queued'],
            [PeppolTransmissionStatus::PROCESSING, 'Processing'],
            [PeppolTransmissionStatus::SENT, 'Sent'],
            [PeppolTransmissionStatus::ACCEPTED, 'Accepted'],
            [PeppolTransmissionStatus::REJECTED, 'Rejected'],
            [PeppolTransmissionStatus::FAILED, 'Failed'],
            [PeppolTransmissionStatus::RETRYING, 'Retrying'],
            [PeppolTransmissionStatus::DEAD, 'Dead'],
        ];
    }

    public static function colorProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, 'gray'],
            [PeppolTransmissionStatus::QUEUED, 'blue'],
            [PeppolTransmissionStatus::PROCESSING, 'yellow'],
            [PeppolTransmissionStatus::SENT, 'indigo'],
            [PeppolTransmissionStatus::ACCEPTED, 'green'],
            [PeppolTransmissionStatus::REJECTED, 'red'],
            [PeppolTransmissionStatus::FAILED, 'orange'],
            [PeppolTransmissionStatus::RETRYING, 'purple'],
            [PeppolTransmissionStatus::DEAD, 'red'],
        ];
    }

    public static function iconProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, 'heroicon-o-clock'],
            [PeppolTransmissionStatus::QUEUED, 'heroicon-o-queue-list'],
            [PeppolTransmissionStatus::PROCESSING, 'heroicon-o-arrow-path'],
            [PeppolTransmissionStatus::SENT, 'heroicon-o-paper-airplane'],
            [PeppolTransmissionStatus::ACCEPTED, 'heroicon-o-check-circle'],
            [PeppolTransmissionStatus::REJECTED, 'heroicon-o-x-circle'],
            [PeppolTransmissionStatus::FAILED, 'heroicon-o-exclamation-triangle'],
            [PeppolTransmissionStatus::RETRYING, 'heroicon-o-arrow-path'],
            [PeppolTransmissionStatus::DEAD, 'heroicon-o-no-symbol'],
        ];
    }

    public static function finalStatusProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, false],
            [PeppolTransmissionStatus::QUEUED, false],
            [PeppolTransmissionStatus::PROCESSING, false],
            [PeppolTransmissionStatus::SENT, false],
            [PeppolTransmissionStatus::ACCEPTED, true],
            [PeppolTransmissionStatus::REJECTED, true],
            [PeppolTransmissionStatus::FAILED, false],
            [PeppolTransmissionStatus::RETRYING, false],
            [PeppolTransmissionStatus::DEAD, true],
        ];
    }

    public static function retryableStatusProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, false],
            [PeppolTransmissionStatus::QUEUED, false],
            [PeppolTransmissionStatus::PROCESSING, false],
            [PeppolTransmissionStatus::SENT, false],
            [PeppolTransmissionStatus::ACCEPTED, false],
            [PeppolTransmissionStatus::REJECTED, false],
            [PeppolTransmissionStatus::FAILED, true],
            [PeppolTransmissionStatus::RETRYING, true],
            [PeppolTransmissionStatus::DEAD, false],
        ];
    }

    public static function awaitingAckProvider(): array
    {
        return [
            [PeppolTransmissionStatus::PENDING, false],
            [PeppolTransmissionStatus::QUEUED, false],
            [PeppolTransmissionStatus::PROCESSING, false],
            [PeppolTransmissionStatus::SENT, true],
            [PeppolTransmissionStatus::ACCEPTED, false],
            [PeppolTransmissionStatus::REJECTED, false],
            [PeppolTransmissionStatus::FAILED, false],
            [PeppolTransmissionStatus::RETRYING, false],
            [PeppolTransmissionStatus::DEAD, false],
        ];
    }

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
    #[DataProvider('labelProvider')]
    public function it_provides_correct_labels(
        PeppolTransmissionStatus $status,
        string $expectedLabel
    ): void {
        $this->assertEquals($expectedLabel, $status->label());
    }

    #[Test]
    #[DataProvider('colorProvider')]
    public function it_provides_correct_colors(
        PeppolTransmissionStatus $status,
        string $expectedColor
    ): void {
        $this->assertEquals($expectedColor, $status->color());
    }

    #[Test]
    #[DataProvider('iconProvider')]
    public function it_provides_correct_icons(
        PeppolTransmissionStatus $status,
        string $expectedIcon
    ): void {
        $this->assertEquals($expectedIcon, $status->icon());
    }

    #[Test]
    #[DataProvider('finalStatusProvider')]
    public function it_correctly_identifies_final_statuses(
        PeppolTransmissionStatus $status,
        bool $expectedIsFinal
    ): void {
        $this->assertEquals($expectedIsFinal, $status->isFinal());
    }

    #[Test]
    #[DataProvider('retryableStatusProvider')]
    public function it_correctly_identifies_retryable_statuses(
        PeppolTransmissionStatus $status,
        bool $expectedCanRetry
    ): void {
        $this->assertEquals($expectedCanRetry, $status->canRetry());
    }

    #[Test]
    #[DataProvider('awaitingAckProvider')]
    public function it_correctly_identifies_awaiting_acknowledgement_status(
        PeppolTransmissionStatus $status,
        bool $expectedIsAwaitingAck
    ): void {
        $this->assertEquals($expectedIsAwaitingAck, $status->isAwaitingAck());
    }

    #[Test]
    public function it_can_be_instantiated_from_value(): void
    {
        $status = PeppolTransmissionStatus::from('sent');

        $this->assertEquals(PeppolTransmissionStatus::SENT, $status);
    }

    #[Test]
    public function it_throws_on_invalid_value(): void
    {
        $this->expectException(ValueError::class);
        PeppolTransmissionStatus::from('invalid');
    }

    #[Test]
    public function it_models_complete_transmission_lifecycle(): void
    {
        // Test typical successful flow
        $pending = PeppolTransmissionStatus::PENDING;
        $this->assertFalse($pending->isFinal());
        $this->assertFalse($pending->canRetry());

        $queued = PeppolTransmissionStatus::QUEUED;
        $this->assertFalse($queued->isFinal());

        $processing = PeppolTransmissionStatus::PROCESSING;
        $this->assertFalse($processing->isFinal());

        $sent = PeppolTransmissionStatus::SENT;
        $this->assertTrue($sent->isAwaitingAck());
        $this->assertFalse($sent->isFinal());

        $accepted = PeppolTransmissionStatus::ACCEPTED;
        $this->assertTrue($accepted->isFinal());
        $this->assertFalse($accepted->canRetry());
    }

    #[Test]
    public function it_models_failure_and_retry_flow(): void
    {
        $failed = PeppolTransmissionStatus::FAILED;
        $this->assertFalse($failed->isFinal());
        $this->assertTrue($failed->canRetry());

        $retrying = PeppolTransmissionStatus::RETRYING;
        $this->assertFalse($retrying->isFinal());
        $this->assertTrue($retrying->canRetry());

        $dead = PeppolTransmissionStatus::DEAD;
        $this->assertTrue($dead->isFinal());
        $this->assertFalse($dead->canRetry());
    }

    #[Test]
    public function it_models_rejection_flow(): void
    {
        $rejected = PeppolTransmissionStatus::REJECTED;
        $this->assertTrue($rejected->isFinal());
        $this->assertFalse($rejected->canRetry());
        $this->assertEquals('red', $rejected->color());
    }
}
