<?php

namespace Modules\Clients\Services;

use Modules\Clients\Services\CustomerService;

use Modules\Clients\Events\CustomerWasCreated;

use Modules\Clients\Events\CustomerWasUpdated;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Services\BaseService;

use Modules\Clients\Models\Relation;

use Illuminate\Database\Eloquent\Model;

class CustomerService extends BaseService
{
    public function model(): string
    {
        return Relation::class;
    }

    public function create(array $validatedInput): Model
    {
        $client = Relation::create($validatedInput);
        event(new CustomerWasCreated());

        return $client;
    }

    public function update(array $input, $client): Relation
    {
        $client = Relation::find($client);
        $client->fill($input);
        $client->save();

        event(new CustomerWasUpdated());

        return $client;
    }
}
