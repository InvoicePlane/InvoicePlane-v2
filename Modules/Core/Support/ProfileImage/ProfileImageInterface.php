<?php

namespace App\Support\ProfileImage;

use Modules\Users\Models\User;

interface ProfileImageInterface
{
    public function getProfileImageUrl(User $user);
}
