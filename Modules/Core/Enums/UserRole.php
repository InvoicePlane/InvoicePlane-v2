<?php

namespace Modules\Core\Enums;

use Modules\Core\Enums\UserRole;

use Modules\Core\Contracts\LabeledEnum;

enum UserRole: string implements \Modules\Core\Contracts\LabeledEnum
{
    case SUPER_ADMIN    = 'super_admin';
    case ADMIN          = 'admin';
    case ASSIST         = 'assist';
    case CUSTOMER_ADMIN = 'client_admin';
    case CUSTOMER       = 'client';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN    => 'Super Admin',
            self::ADMIN          => 'admin',
            self::ASSIST         => 'assist',
            self::CUSTOMER_ADMIN => 'Client Admin',
            self::CUSTOMER       => 'client',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN    => 'maroon',
            self::ADMIN          => 'green',
            self::ASSIST         => 'warning',
            self::CUSTOMER_ADMIN => 'info',
            self::CUSTOMER       => 'gray',
        };
    }
}
