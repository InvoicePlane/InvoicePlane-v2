<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolTransmissionStatus: string implements LabeledEnum
{
    use HasOptions;

    case PENDING    = 'pending';
    case QUEUED     = 'queued';
    case PROCESSING = 'processing';
    case SENT       = 'sent';
    case ACCEPTED   = 'accepted';
    case REJECTED   = 'rejected';
    case FAILED     = 'failed';
    case RETRYING   = 'retrying';
    case DEAD       = 'dead';

    /**
     * Get the human-readable label for the current transmission status.
     *
     * @return string The label corresponding to the enum case (e.g., 'Pending', 'Sent', 'Accepted').
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING    => 'Pending',
            self::QUEUED     => 'Queued',
            self::PROCESSING => 'Processing',
            self::SENT       => 'Sent',
            self::ACCEPTED   => 'Accepted',
            self::REJECTED   => 'Rejected',
            self::FAILED     => 'Failed',
            self::RETRYING   => 'Retrying',
            self::DEAD       => 'Dead',
        };
    }

    /**
     * Get the UI color name associated with the transmission status.
     *
     * @return string The color name (CSS/tailwind-style) representing this status, e.g. 'gray', 'blue', 'green', 'red'.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING    => 'gray',
            self::QUEUED     => 'blue',
            self::PROCESSING => 'yellow',
            self::SENT       => 'indigo',
            self::ACCEPTED   => 'green',
            self::REJECTED   => 'red',
            self::FAILED     => 'orange',
            self::RETRYING   => 'purple',
            self::DEAD       => 'red',
        };
    }

    /**
     * Get the Heroicon identifier representing the transmission status.
     *
     * @return string the Heroicon identifier corresponding to the enum case
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING    => 'heroicon-o-clock',
            self::QUEUED     => 'heroicon-o-queue-list',
            self::PROCESSING => 'heroicon-o-arrow-path',
            self::SENT       => 'heroicon-o-paper-airplane',
            self::ACCEPTED   => 'heroicon-o-check-circle',
            self::REJECTED   => 'heroicon-o-x-circle',
            self::FAILED     => 'heroicon-o-exclamation-triangle',
            self::RETRYING   => 'heroicon-o-arrow-path',
            self::DEAD       => 'heroicon-o-no-symbol',
        };
    }

    /**
     * Determine whether the transmission status is final.
     *
     * @return bool `true` if the status is `ACCEPTED`, `REJECTED`, or `DEAD`, `false` otherwise
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::ACCEPTED,
            self::REJECTED,
            self::DEAD,
        ]);
    }

    /**
     * Determines whether the transmission status permits a retry.
     *
     * @return bool `true` if the status is FAILED or RETRYING, `false` otherwise
     */
    public function canRetry(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::RETRYING,
        ]);
    }

    /**
     * Indicates the status is awaiting acknowledgment.
     *
     * @return bool `true` if the status is awaiting acknowledgment (SENT), `false` otherwise
     */
    public function isAwaitingAck(): bool
    {
        return $this === self::SENT;
    }
}
