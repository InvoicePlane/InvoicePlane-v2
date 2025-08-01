<?php

namespace Modules\Clients\Database\Seeders;

use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;

class ContactsSeeder extends AbstractSeeder
{
    protected string $label        = 'Contacts';
    protected int    $defaultCount = 5;

    protected function buildOne(): void
    {
        $relation = Relation::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();

        Contact::factory()
            ->state([
                'company_id'  => $this->companyId,
                'relation_id' => $relation->id,
            ])
            ->create();
    }
}
