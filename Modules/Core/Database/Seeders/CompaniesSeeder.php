<?php

namespace Modules\Core\Database\Seeders;

use Modules\Clients\Enums\RelationType;

use Modules\Core\Database\Seeders\CompaniesSeeder;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Models\Relation;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        Company::factory()
            ->count(1)
            ->has(
                /*Relation::factory()
                    ->count(25)
                    ->state([
                        'relation_type' => RelationType::CUSTOMER->value,
                    ]),
                'relations'*/
            )
            ->create();
    }
}
