<?php

namespace Modules\Clients\Database\Seeders;

use Modules\Clients\Models\Address;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;

class RelationsSeeder extends AbstractSeeder
{
    protected string $label = 'Relations';

    protected int    $defaultCount = 5;

    protected function buildOne(): void
    {
        $relation = Relation::factory()
            ->state(['company_id' => $this->companyId])
            ->create();

        Contact::factory()
            ->state([
                'company_id'  => $this->companyId,
                'relation_id' => $relation->id,
            ])
            ->create();

        Address::factory()
            ->state([
                'company_id'       => $this->companyId,
                'addressable_id'   => $relation->id,
                'addressable_type' => Relation::class,
            ])
            ->create();
    }
}
