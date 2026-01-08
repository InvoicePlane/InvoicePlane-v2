<?php

namespace Modules\Core\Enums;

enum UserType: int
{
    /**
     * The user admin role with read and write
     * privileges.
     */
    case ADMIN = 1;

    /**
     * The user with guest read only privilege
     * known in IPv1.5 as guest_read_only.
     */
    case CLIENT = 2;

    public function getLabel(): string
    {
        return match($this) {
            self::ADMIN  => 'ip.administrator',
            self::CLIENT => 'ip.client',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ADMIN  => 'blue-500',
            self::CLIENT => 'gray-500',
        };
    }
}
