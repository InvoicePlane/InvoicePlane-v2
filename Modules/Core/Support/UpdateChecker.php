<?php

namespace Modules\Core\Support;

use Modules\Core\Support\UpdateChecker;

class UpdateChecker
{
    protected $currentVersion;

    public function __construct()
    {
        $check_url            = 'https://ids.invoiceplane.com/updatecheck?cv=' . config('ip.version');
        $this->currentVersion = file_get_contents($check_url);
    }

    /**
     * Check to see if there is a newer version available for download.
     *
     * @return bool
     */
    public function updateAvailable()
    {
        return (bool) (str_replace('-', '', $this->currentVersion) > str_replace('-', '', config('ip.version')));
    }

    /**
     * Getter for current version.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }
}
