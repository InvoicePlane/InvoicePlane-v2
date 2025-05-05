<?php

namespace App\IpModules\Exports\Support\Results;

interface SourceInterface
{
    public function getResults($params = []);
}
