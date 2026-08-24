<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum MailType: string implements LabeledEnum
{
    case REMINDER = 'reminder';
    case SENT = 'sent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::REMINDER => 'Reminder',
            self::SENT => 'Sent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REMINDER => 'warning',
            self::SENT => 'success',
        };
    }
}
