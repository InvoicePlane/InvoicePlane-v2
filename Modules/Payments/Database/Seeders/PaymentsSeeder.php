<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends Seeder
{
    protected array $paymentMethods = [
        'bank_transfer',
        'credit_card',
        'paypal',
        'stripe',
        'cash',
        'check',
        'other',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Payment::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping payments for company {$company->name} - already has {$existingCount} payments.");

                return;
            }

            $this->command->info("Creating payments for company: {$company->name}");

            $invoices = Invoice::query()->where('company_id', $company->id)
                ->where('invoice_status', InvoiceStatus::PAID->value)
                ->where('invoice_total', '>', 0)
                ->get();

            if ($invoices->isEmpty()) {
                $this->command->warn("No paid invoices found for company {$company->name}. Creating some...");
                $this->call(InvoicesSeeder::class, ['companyId' => $company->id]);
                $invoices = Invoice::query()->where('company_id', $company->id)
                    ->where('invoice_status', InvoiceStatus::PAID->value)
                    ->where('invoice_total', '>', 0)
                    ->get();
            }

            foreach ($invoices as $invoice) {
                $this->createPaymentForInvoice($invoice);
            }
            $this->createPartialPayments($company);

            $this->command->info('Created ' . Payment::query()->where('company_id', $company->id)->count() . " payments for company: {$company->name}");
        });
    }

    protected function createPaymentForInvoice(Invoice $invoice): void
    {
        $paymentDate   = $invoice->invoice_date->addDays(random_int(0, 30));
        $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];
        $reference     = mb_strtoupper(mb_substr($paymentMethod, 0, 3)) . '-' . rand(1000, 9999);

        // 'reference'      => $reference,
        Payment::factory()
            ->for($invoice->company)
            ->for($invoice->customer, 'customer')
            ->for($invoice, 'invoice')
            ->create([
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::COMPLETED->value,
                'paid_at'        => $paymentDate,
                'payment_amount' => $invoice->total,
                'notes'          => $this->getRandomNotes(),
            ]);
    }

    protected function createPartialPayments(Company $company): void
    {
        $invoices = Invoice::query()->where('company_id', $company->id)
            ->where('invoice_status', '!=', InvoiceStatus::PAID->value)
            ->where('invoice_total', '>', 0)
            ->inRandomOrder()
            ->limit(random_int(5, 15))
            ->get();

        foreach ($invoices as $invoice) {
            $paymentCount    = rand(1, 3);
            $remainingAmount = $invoice->total - $invoice->paid_amount;

            for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
                $paymentAmount = min($remainingAmount, $remainingAmount * (random_int(20, 80) / 100));
                $remainingAmount -= $paymentAmount;

                $paymentDate   = $invoice->invoice_date->addDays(random_int(0, 60));
                $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];
                $reference     = mb_strtoupper(mb_substr($paymentMethod, 0, 3)) . '-' . rand(1000, 9999);

                Payment::factory()
                    ->for($invoice->company)
                    ->for($invoice->customer, 'customer')
                    ->for($invoice, 'invoice')
                    ->create([
                        'payment_method' => $paymentMethod,
                        'payment_status' => PaymentStatus::COMPLETED->value,
                        'paid_at'        => $paymentDate,
                        'payment_amount' => $paymentAmount,
                        'reference'      => $reference,
                        'notes'          => $this->getRandomNotes(),
                    ]);

                $invoice->increment('paid_amount', $paymentAmount);

                if (abs($invoice->paid_amount - $invoice->total) < 0.01) {
                    $invoice->update([
                        'invoice_status' => InvoiceStatus::PAID->value,
                        'paid_status'    => 'paid',
                        'due_amount'     => 0,
                    ]);
                    break;
                }
                $invoice->update([
                    'invoice_status' => InvoiceStatus::PARTIALLY_PAID->value,
                    'paid_status'    => 'partially_paid',
                    'due_amount'     => $invoice->total - $invoice->paid_amount,
                ]);
            }
        }
    }

    protected function getRandomNotes(): ?string
    {
        $notes = [
            'Payment received, thank you!',
            'Paid via bank transfer',
            'Payment received with thanks',
            'Customer payment',
            'Invoice payment',
            'Partial payment received',
            'Final payment',
            'Advance payment',
            'Payment for services rendered',
            null,
        ];

        return $notes[array_rand($notes)];
    }
}
