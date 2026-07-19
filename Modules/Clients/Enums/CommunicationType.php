<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum CommunicationType: string implements LabeledEnum
{
    case EMAIL      = 'email';
    case PHONE      = 'phone';
    case FAX        = 'fax';
    case MOBILE     = 'mobile';
    case WHATSAPP   = 'whatsapp';
    case INVOICE_CC = 'invoice_cc';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function ccTypes(): array
    {
        return [self::INVOICE_CC->value];
    }

    public function label(): string
    {
        return match ($this) {
            self::EMAIL      => 'Email',
            self::PHONE      => 'Phone',
            self::FAX        => 'Fax',
            self::MOBILE     => 'Mobile',
            self::WHATSAPP   => 'Whatsapp',
            self::INVOICE_CC => 'Invoice CC Email',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EMAIL      => 'info',
            self::PHONE      => 'primary',
            self::FAX        => 'gray',
            self::MOBILE     => 'success',
            self::WHATSAPP   => 'amber',
            self::INVOICE_CC => 'warning',
        };
    }
}
