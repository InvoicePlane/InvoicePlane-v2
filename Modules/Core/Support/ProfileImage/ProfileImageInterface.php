<?php

namespace App\Support\ProfileImage;

use App\IpModules\Users\Models\User;

interface ProfileImageInterface
{
    public function getProfileImageUrl(User $user);
}
