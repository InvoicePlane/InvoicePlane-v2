<?php

namespace Modules\Invoices\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupsTableSeeder extends Seeder
{
    public function run(): void
    {
        InvoiceGroup::factory()->count(5)->create();
    }
}
