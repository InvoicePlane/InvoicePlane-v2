<?php

namespace Modules\Core\Enums;

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
            self::EMAIL    => trans('enums.communication_type.email'),
            self::PHONE    => trans('enums.communication_type.phone'),
            self::FAX      => trans('enums.communication_type.fax'),
            self::MOBILE   => trans('enums.communication_type.mobile'),
            self::WHATSAPP => trans('enums.communication_type.whatsapp'),
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
