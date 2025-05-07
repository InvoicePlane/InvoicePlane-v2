<?php

namespace Modules\Core\Support\ProfileImage\Drivers;

use Modules\Core\Support\ProfileImage\ProfileImageInterface;

use Modules\Core\Support\ProfileImage\Drivers\Gravatar;

use Modules\Core\Models\User;

use Modules\Core\Support\ProfileImage\ProfileImageInterface;
use Modules\Users\Models\User;

class Gravatar implements ProfileImageInterface
{
    public function getProfileImageUrl(User $user)
    {
        return 'https://www.gravatar.com/avatar/' . md5(mb_strtolower($user->email)) . '?d=mm';
    }
}
