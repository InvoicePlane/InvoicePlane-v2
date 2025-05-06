<?php

namespace Modules\Core\Support\Results;

class Clients implements SourceInterface
{
    public function getResults($params = [])
    {
        $client = Customer::orderBy('name');

        return $client->get()->toArray();
    }
}
