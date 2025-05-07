<?php

namespace Modules\Core\Support\ProfileImage;

use Modules\Core\Models\User;
use Modules\Users\Models\User;

interface ProfileImageInterface
{
    public function getProfileImageUrl(User $user);
}
