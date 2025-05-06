<?php

namespace Modules\Core\Support\ProfileImage;

use Modules\Users\Models\User;

interface ProfileImageInterface
{
    public function getProfileImageUrl(User $user);
}
