<?php

namespace Modules\Clients\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Events\CustomerWasCreated;
use Modules\Clients\Events\CustomerWasUpdated;
use Modules\Clients\Models\Relation;
use Modules\Core\Services\BaseService;

class CustomerService extends BaseService
{
    public function model(): string
    {
        return Relation::class;
    }

    public function createCustomer(array $validatedInput): Model
    {
        $customer = Relation::query()->create($validatedInput);
        event(new CustomerWasCreated());

        return $customer;
    }

    public function updateCustomer($customer, array $input): Relation
    {
        $customer->fill($input);
        $customer->save();

        event(new CustomerWasUpdated());

        return $customer;
    }
}
