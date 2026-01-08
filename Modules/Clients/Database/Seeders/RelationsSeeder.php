<?php

namespace Modules\Clients\Database\Seeders;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;

class RelationsSeeder extends AbstractSeeder
{
    protected string $label = 'Relations';

    protected int $defaultCount = 25;

    protected function buildOne(): void
    {
        Relation::factory()
            ->state([
                'company_id' => $this->companyId,
            ])
            ->create();
    }
}
