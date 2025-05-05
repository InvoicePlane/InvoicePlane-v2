<?php

namespace App\IpModules\Exports\Support\Results;

use App\IpModules\Customers\Models\Customer;

class Clients implements SourceInterface
{
    public function getResults($params = [])
    {
        $client = Customer::orderBy('name');

        return $client->get()->toArray();
    }
}
