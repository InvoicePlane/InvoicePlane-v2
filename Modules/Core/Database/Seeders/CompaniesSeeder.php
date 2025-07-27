<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;

class CompaniesSeeder extends Seeder
{
    public function run(int $count = 1): void
    {
        // Create the default company
        Company::factory()
            ->create([
                'search_code'      => 'ivplv2',
                'name'             => 'InvoicePlane Corporation',
                'slug'             => 'invoiceplane-corporation',
                'vat_number'       => 'US0123456789',
                'id_number'        => '1234567890',
                'coc_number'       => '12345678',
                'quote_template'   => 'default',
                'invoice_template' => 'default',
            ]);

        // Create additional companies if count > 1
        if ($count > 1) {
            Company::factory()
                ->count($count - 1)
                ->create();
        }

        $this->command->info("Created {$count} companies");
    }
}
