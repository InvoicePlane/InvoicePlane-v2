<?php

namespace Modules\Core\Helpers;

use Modules\Core\Helpers\PathsHelper;

class PathsHelper
{
    public function addon_path($path = '')
    {
        return base_path('custom/addons') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function attachment_path($path = '')
    {
        return storage_path('attachments') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
