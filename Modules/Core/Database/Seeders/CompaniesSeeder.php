<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Models\Company;

class CompaniesSeeder extends AbstractSeeder
{
    public function buildOne(int $count = 1): void
    {
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

        if ($count > 1) {
            Company::factory()
                ->count($count - 1)
                ->create();
        }
    }
}
