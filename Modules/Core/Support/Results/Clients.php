<?php

namespace Modules\Core\Support\Results;

use Modules\Clients\Models\Relation;

class Clients implements SourceInterface
{
    public function getResults($params = [])
    {
        $client = Customer::orderBy('name');

        return $client->get()->toArray();
    }
}
