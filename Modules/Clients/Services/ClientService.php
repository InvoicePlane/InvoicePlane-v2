<?php

namespace Modules\Clients\Services;

use Modules\Clients\Events\ClientWasCreated;
use Modules\Clients\Events\ClientWasUpdated;
use Modules\Clients\Models\Client;
use Modules\Core\Services\BaseService;

class ClientService extends BaseService
{
    public function model(): string
    {
        return Client::class;
    }

    public function create(array $validatedInput): Client
    {
        $client = Client::create($validatedInput);
        event(new ClientWasCreated());

        return $client;
    }

    public function update(array $input, $client): Client
    {
        $client = Client::find($client);
        $client->fill($input);
        $client->save();

        event(new ClientWasUpdated());

        return $client;
    }
}
