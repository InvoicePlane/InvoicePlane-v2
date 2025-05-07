<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
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
