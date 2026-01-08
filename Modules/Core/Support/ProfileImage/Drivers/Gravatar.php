<?php

namespace Modules\Core\Support\ProfileImage\Drivers;

use Modules\Core\Models\User;
use Modules\Core\Support\ProfileImage\ProfileImageInterface;

class Gravatar implements ProfileImageInterface
{
    public function getProfileImageUrl(User $user)
    {
        return 'https://www.gravatar.com/avatar/' . md5(mb_strtolower($user->email)) . '?d=mm';
    }
}
