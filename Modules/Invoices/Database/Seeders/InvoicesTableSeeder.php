<?php

namespace Modules\Invoices\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Models\InvoiceItemAmount;

class InvoicesTableSeeder extends Seeder
{
    public function run(): void
    {
        Invoice::factory()->count(15)->create()->each(function ($invoice): void {
            $invoice->invoiceItems()->saveMany(
                InvoiceItem::factory()->count(rand(3, 5))->create()->each(function ($invoiceItem): void {
                    $invoiceItem->invoiceItemAmounts()->saveMany(
                        InvoiceItemAmount::factory()->count(rand(3, 5))->create()
                    )->make();
                })
            )->make();
        });
    }
}
