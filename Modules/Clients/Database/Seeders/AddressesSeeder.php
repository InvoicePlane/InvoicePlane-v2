<?php

namespace Modules\Clients\Database\Seeders;

use Modules\Clients\Models\Address;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;

class AddressesSeeder extends AbstractSeeder
{
    protected string $label = 'Addresses';

    protected int    $defaultCount = 5;

    protected function buildOne(): void
    {
        $relation = Relation::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();

        Address::factory()
            ->state([
                'company_id'       => $this->companyId,
                'addressable_id'   => $relation->id,
                'addressable_type' => Relation::class,
            ])
            ->create();
    }
}
