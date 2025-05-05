<?php

namespace App\Support\ProfileImage\Drivers;

use App\IpModules\Users\Models\User;
use App\Support\ProfileImage\ProfileImageInterface;

class Gravatar implements ProfileImageInterface
{
    public function getProfileImageUrl(User $user)
    {
        return 'https://www.gravatar.com/avatar/' . md5(mb_strtolower($user->email)) . '?d=mm';
    }
}
