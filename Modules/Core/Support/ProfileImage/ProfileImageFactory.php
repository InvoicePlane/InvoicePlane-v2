<?php

namespace Modules\Core\Support\ProfileImage;

class ProfileImageFactory
{
    public static function create()
    {
        $class = 'Modules\Core\Support\ProfileImage\Drivers\\' . config('ip.profileImageDriver', 'Gravatar');

        return new $class();
    }
}
