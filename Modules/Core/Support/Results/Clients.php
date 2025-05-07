<?php

namespace Modules\Core\Support\Results;

use Modules\Core\Support\Results\SourceInterface;

use Modules\Core\Support\Results\Clients;

class Clients implements SourceInterface
{
    public function getResults($params = [])
    {
        $client = Customer::orderBy('name');

        return $client->get()->toArray();
    }
}
