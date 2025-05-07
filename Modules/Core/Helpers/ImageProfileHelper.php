<?php

use Modules\Core\Support\ProfileImage\ProfileImageFactory;

class ImageProfileHelper
{
    public function profileImageUrl($user)
    {
        $profileImage = ProfileImageFactory::create();

        return $profileImage->getProfileImageUrl($user);
    }
}
