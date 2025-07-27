<?php

namespace Modules\Invoices\Database\Seeders;

use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Products\Models\Product;

class InvoicesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Invoice::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping invoices for company {$company->name} - already has {$existingCount} invoices.");

                return;
            }

            $this->command->info("Creating invoices for company: {$company->name}");

            $customers = Relation::query()->where('company_id', $company->id)
                ->where('relation_type', 'customer')
                ->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)
                    ->where('relation_type', 'customer')
                    ->get();
            }

            $products = Product::query()->where('company_id', $company->id)->get();

            if ($products->isEmpty()) {
                $this->command->warn("No products found for company {$company->name}. Creating some...");
                $this->call(ProductsSeeder::class, ['companyId' => $company->id]);
                $products = Product::query()->where('company_id', $company->id)->get();
            }

            $taxRates = TaxRate::query()->where('company_id', $company->id)->get();

            if ($taxRates->isEmpty()) {
                $this->command->warn("No tax rates found for company {$company->name}. Using default...");
                $taxRates = collect([
                    TaxRate::factory()->create([
                        'company_id' => $company->id,
                        'name'       => 'VAT',
                        'rate'       => 21.0,
                        'is_default' => true,
                    ]),
                ]);
            }

            $invoiceCount = rand(20, 50);

            for ($i = 0; $i < $invoiceCount; $i++) {
                $invoice = $this->createInvoice($company, $customers->random(), $products, $taxRates);
                $this->addInvoiceItems($invoice, $products, $taxRates);
            }

            $this->command->info("Created {$invoiceCount} invoices for company: {$company->name}");
        });
    }

    protected function createInvoice(Company $company, Relation $customer, $products, $taxRates): \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        $statuses = [
            InvoiceStatus::DRAFT,
            InvoiceStatus::SENT,
            InvoiceStatus::VIEWED,
            InvoiceStatus::PAID,
            InvoiceStatus::OVERDUE,
        ];

        $status      = $statuses[array_rand($statuses)];
        $invoiceDate = now()->subDays(random_int(0, 90));
        $dueDate     = $invoiceDate->copy()->addDays(random_int(15, 60));
        // Create the invoice with initial values, they will be updated by addInvoiceItems
        $discountAmount = random_int(0, 1) ? random_int(5, 20) : 0;
        $discountType   = $discountAmount > 0 ? (random_int(0, 1) ? 'percentage' : 'fixed') : null;

        return Invoice::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->create([
                'invoice_number'           => $this->generateInvoiceNumber($company->id),
                'invoice_status'           => $status->value,
                'invoice_sign'             => '1',
                'invoiced_at'              => $invoiceDate->format('Y-m-d'),
                'invoice_due_at'           => $dueDate->format('Y-m-d'),
                'invoice_discount_amount'  => 0, // Will be updated by addInvoiceItems
                'invoice_discount_percent' => $discountType === 'percentage' ? $discountAmount : 0,
                'invoice_item_subtotal'    => 0, // Will be updated by addInvoiceItems
                'item_tax_total'           => 0, // Will be updated by addInvoiceItems
                'invoice_tax_total'        => 0, // Will be updated by addInvoiceItems
                'invoice_total'            => 0, // Will be updated by addInvoiceItems
                'url_key'                  => bin2hex(random_bytes(16)),
                'is_read_only'             => false,
                'terms'                    => $this->getRandomTerms(),
                'footer'                   => $this->getRandomFooter(),
                'summary'                  => 'Thank you for your business!',
            ]);
    }

    protected function addInvoiceItems(Invoice $invoice, $products, $taxRates): void
    {
        $itemCount   = min(random_int(1, 10), $products->count());
        $productPool = $products->shuffle();
        $subtotal    = 0;
        $taxTotal    = 0;

        for ($i = 0; $i < $itemCount; $i++) {
            $product      = $productPool->get($i);
            $quantity     = rand(1, 5);
            $unitPrice    = $product->price * (random_int(90, 110) / 100);
            $discount     = rand(0, 1) === 1 ? rand(5, 20) : 0;
            $discountType = $discount > 0 ? (random_int(0, 1) ? 'percentage' : 'fixed') : null;

            // Get random tax rates if applicable
            $taxRate1   = null;
            $taxRate2   = null;
            $taxRate1Id = null;
            $taxRate2Id = null;

            if ($taxRates->isNotEmpty()) {
                $taxRate1   = $taxRates->random();
                $taxRate1Id = $taxRate1->id;

                if (random_int(0, 1) === 1 && $taxRates->count() > 1) {
                    $taxRate2   = $taxRates->where('id', '!=', $taxRate1->id)->random();
                    $taxRate2Id = $taxRate2->id;
                }
            }

            $itemSubtotal = $quantity * $unitPrice;
            $itemTax1     = $taxRate1 ? ($itemSubtotal * ($taxRate1->rate / 100)) : 0;
            $itemTax2     = $taxRate2 ? ($itemSubtotal * ($taxRate2->rate / 100)) : 0;
            $itemTaxTotal = $itemTax1 + $itemTax2;

            if ($discount > 0) {
                $discountValue = $discountType === 'percentage'
                    ? $itemSubtotal * ($discount / 100)
                    : $discount;
                $itemSubtotal -= $discountValue;
            }

            $item = new InvoiceItem([
                'company_id'    => $invoice->company_id,
                'invoice_id'    => $invoice->id,
                'product_id'    => $product->id,
                'item_name'     => $product->name,
                'price'         => $unitPrice,
                'discount'      => $discount,
                'tax_rate_id'   => $taxRate1Id,
                'tax_rate_2_id' => $taxRate2Id,
                'tax_1'         => $itemTax1,
                'tax_2'         => $itemTax2,
                'tax_total'     => $itemTaxTotal,
                'subtotal'      => $itemSubtotal,
                'total'         => $itemSubtotal + $itemTaxTotal,
                'description'   => $product->description,
            ]);

            $invoice->invoiceItems()->save($item);
            $subtotal += $itemSubtotal;
            $taxTotal += $itemTaxTotal;
        }

        $invoice->update([
            'invoice_item_subtotal' => $subtotal,
            'item_tax_total'        => $taxTotal,
            'invoice_tax_total'     => $taxTotal,
            'invoice_total'         => $subtotal + $taxTotal,
        ]);
    }

    protected function generateInvoiceNumber(int $companyId): string
    {
        $prefix      = 'INV-' . date('Y') . '-';
        $lastInvoice = Invoice::query()->where('company_id', $companyId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) str_replace($prefix, '', $lastInvoice->invoice_number);

            return $prefix . mb_str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }

    protected function getRandomTerms(): string
    {
        $terms = [
            'Payment due within 30 days of invoice date.',
            'Net 30 terms. Late payments subject to 1.5% monthly interest.',
            'Please make checks payable to our company name.',
            'Thank you for your business!',
            'All prices are in USD.',
            'No returns or refunds after 30 days.',
            'A 2% discount is available if paid within 10 days.',
            'Payment is due upon receipt of this invoice.',
        ];

        return $terms[array_rand($terms)];
    }

    protected function getRandomFooter(): string
    {
        $footers = [
            'Thank you for your business!',
            'Questions? Contact our billing department at billing@example.com',
            'Make all checks payable to [Company Name]',
            'Thank you for your prompt payment!',
            'This is a computer-generated invoice. No signature required.',
        ];

        return $footers[array_rand($footers)];
    }
}
