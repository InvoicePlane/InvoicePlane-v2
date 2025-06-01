<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum CommunicationType: string implements LabeledEnum
{
    case EMAIL    = 'email';
    case PHONE    = 'phone';
    case FAX      = 'fax';
    case MOBILE   = 'mobile';
    case WHATSAPP = 'whatsapp';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::EMAIL    => 'Email',
            self::PHONE    => 'Phone',
            self::FAX      => 'Fax',
            self::MOBILE   => 'Mobile',
            self::WHATSAPP => 'Whatsapp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EMAIL    => 'info',
            self::PHONE    => 'primary',
            self::FAX      => 'gray',
            self::MOBILE   => 'success',
            self::WHATSAPP => 'amber',
        };
    }
}
