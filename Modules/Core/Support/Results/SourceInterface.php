<?php

namespace Modules\Core\Support\Results;

use Modules\Core\Support\Results\SourceInterface;

interface SourceInterface
{
    public function getResults($params = []);
}
