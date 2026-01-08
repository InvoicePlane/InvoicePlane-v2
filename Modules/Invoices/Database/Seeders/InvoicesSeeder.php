<?php

namespace Modules\Invoices\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

class InvoicesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Invoice::factory()->count(5)->create(['company_id' => $company->id])->each(function ($invoice) use ($company): void {
                $invoice->invoiceItems()->saveMany(
                    InvoiceItem::factory(['company_id' => $company->id])->count(random_int(2, 3))->create()
                )->make();
            });
        });
    }
}
