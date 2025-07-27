<?php

namespace Modules\Invoices\Database\Seeders;

use Carbon\Carbon;
use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Invoices\Enums\RecurringFrequency;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Products\Models\Product;

class RecurringInvoicesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $terms = [
        '50% deposit required',
        'Due on receipt',
        'Net 30',
        'Payment due upon completion',
        'Payment due within 15 days',
    ];

    protected array $footers = [
        'Thank you for your business!',
        'We appreciate your continued business.',
        'Questions? Contact our billing department.',
        'Terms and conditions apply.',
        'Please pay within the specified time frame.',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = RecurringInvoice::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping recurring invoices for company {$company->name} - already has {$existingCount} recurring invoices.");

                return;
            }

            $this->command->info("Creating recurring invoices for company: {$company->name}");

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

            $users = User::query()->where('company_id', $company->id)->get();

            if ($users->isEmpty()) {
                $this->command->warn("No users found for company {$company->name}. Creating some...");
                $this->call(UsersSeeder::class, ['companyId' => $company->id]);
                $users = User::query()->where('company_id', $company->id)->get();
            }

            $recurringInvoiceCount = rand(3, 10);

            for ($i = 0; $i < $recurringInvoiceCount; $i++) {
                $this->createRecurringInvoice($company, $customers->random(), $products, $taxRates, $users->random());
            }

            $this->command->info("Created {$recurringInvoiceCount} recurring invoices for company: {$company->name}");
        });
    }

    protected function createRecurringInvoice(Company $company, $customer, $products, $taxRates, $user): void
    {
        $frequencies = [
            'daily',
            'weekly',
            'monthly',
            'quarterly',
            'yearly',
        ];

        $frequency = $frequencies[array_rand($frequencies)];
        $startDate = now()->subDays(random_int(0, 90));
        $endDate   = rand(0, 1) ? $startDate->copy()->addYear() : null;

        // Create an invoice first since recurring_invoices references an invoice
        $invoice = \Modules\Invoices\Models\Invoice::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->for($user, 'user')
            ->create([
                'invoice_status_id' => 'draft',
                'invoice_date'      => now(),
                'due_date'          => now()->addDays(30),
            ]);

        // Create the recurring invoice with only the fields that exist in the database
        $recurringInvoice = RecurringInvoice::create([
            'company_id'        => $company->id,
            'customer_id'       => $customer->id,
            'invoice_id'        => $invoice->id,
            'document_group_id' => null, // Can be set if needed
            'frequency'         => $frequency,
            'start_at'          => $startDate->format('Y-m-d'),
            'end_at'            => $endDate ? $endDate->format('Y-m-d') : null,
        ]);

        $this->addRecurringInvoiceItems($recurringInvoice, $products, $taxRates);
    }

    protected function addRecurringInvoiceItems($recurringInvoice, $products, $taxRates): void
    {
        $itemCount   = rand(1, 5);
        $productPool = $products->shuffle();

        // Get a primary tax rate and a secondary one if available
        $primaryTaxRate   = $taxRates->isNotEmpty() ? $taxRates->random() : null;
        $secondaryTaxRate = $taxRates->count() > 1 ? $taxRates->where('id', '!=', $primaryTaxRate?->id)->random() : null;

        for ($i = 0; $i < min($itemCount, $productPool->count()); $i++) {
            $product   = $productPool->get($i);
            $quantity  = rand(1, 5);
            $unitPrice = $product->price * (random_int(90, 110) / 100);
            $subtotal  = $quantity * $unitPrice;

            // Calculate taxes
            $tax1Rate = $primaryTaxRate ? $primaryTaxRate->rate / 100 : 0.21; // Default to 21%
            $tax2Rate = $secondaryTaxRate ? $secondaryTaxRate->rate / 100 : 0;

            $tax1Amount = $subtotal * $tax1Rate;
            $tax2Amount = $subtotal * $tax2Rate;
            $taxTotal   = $tax1Amount + $tax2Amount;
            $total      = $subtotal + $taxTotal;

            // Create recurring invoice item
            $recurringInvoice->items()->create([
                'item_id'       => $product->id,
                'tax_rate_id'   => $primaryTaxRate ? $primaryTaxRate->id : 0,
                'tax_rate_2_id' => $secondaryTaxRate ? $secondaryTaxRate->id : 0,
                'item_name'     => $product->name,
                'quantity'      => $quantity,
                'price'         => $unitPrice,
                'subtotal'      => $subtotal,
                'tax_1'         => $tax1Amount,
                'tax_2'         => $tax2Amount,
                'tax_total'     => $taxTotal,
                'total'         => $total,
                'display_order' => $i + 1,
                'description'   => $product->description,
            ]);
        }
    }

    protected function calculateNextInvoiceDate(Carbon $startDate, RecurringFrequency $frequency): Carbon
    {
        $now = now();

        if ($startDate > $now) {
            return $startDate;
        }

        $nextDate = $startDate->copy();

        while ($nextDate <= $now) {
            $nextDate = match($frequency) {
                RecurringFrequency::DAILY     => $nextDate->addDay(),
                RecurringFrequency::MONTHLY   => $nextDate->addMonth(),
                RecurringFrequency::QUARTERLY => $nextDate->addQuarter(),
                RecurringFrequency::WEEKLY    => $nextDate->addWeek(),
                RecurringFrequency::YEARLY    => $nextDate->addYear(),
                default                       => $nextDate->addMonth(),
            };
        }

        return $nextDate;
    }
}
