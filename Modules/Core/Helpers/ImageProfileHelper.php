<?php
namespace Modules\Core\Helpers;

use Modules\Core\Support\ProfileImageFactory;

class ImageProfileHelper
{
    public function profileImageUrl($user)
    {
        $profileImage = ProfileImageFactory::create();

        return $profileImage->getProfileImageUrl($user);
    }
}
