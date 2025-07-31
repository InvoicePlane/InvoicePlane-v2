<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Support\Facades\Log;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Core\Models\Company;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends AbstractSeeder
{
    protected array $paymentMethods = [
        'bank_transfer',
        'cash',
        'check',
        'credit_card',
        'paypal',
        'stripe',
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
                Log::info("Skipping payments for company {$company->name} - already has {$existingCount} payments.");

                return;
            }

            Log::info("Creating payments for company: {$company->name}");

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

            Log::info('Created ' . Payment::query()->where('company_id', $company->id)->count() . " payments for company: {$company->name}");
        });
    }

    protected function createPaymentForInvoice(Invoice $invoice): void
    {
        // Ensure invoice has a valid total
        if (null === $invoice->invoice_total || $invoice->invoice_total <= 0) {
            $this->command->warn(sprintf(
                'Skipping payment for invoice %s - invalid total: %s',
                $invoice->invoice_number,
                $invoice->invoice_total ?? 'null'
            ));

            return;
        }

        $paymentDate   = $invoice->invoiced_at->addDays(random_int(0, 30));
        $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];
        $reference     = mb_strtoupper(mb_substr($paymentMethod, 0, 3)) . '-' . rand(1000, 9999);

        Payment::factory()
            ->for($invoice->company)
            ->for($invoice->customer, 'customer')
            ->for($invoice, 'invoice')
            ->create([
                'payment_reference' => $reference,
                'payment_method'    => $paymentMethod,
                'payment_status'    => PaymentStatus::COMPLETED->value,
                'paid_at'           => $paymentDate,
                'payment_amount'    => $invoice->invoice_total,
                'notes'             => $this->getRandomNotes(),
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
            $remainingAmount = $invoice->invoice_total - $invoice->paid_amount;

            for ($i = 0; $i < $paymentCount && $remainingAmount > 0.01; $i++) {
                // Calculate payment amount as a percentage of remaining, but ensure it's at least 0.01
                $paymentPercent = random_int(20, 80) / 100;
                $paymentAmount  = round($remainingAmount * $paymentPercent, 2);

                // For the last payment, use the exact remaining amount to avoid rounding issues
                if ($i === $paymentCount - 1 || $paymentAmount < 0.01) {
                    $paymentAmount = $remainingAmount;
                }

                // Ensure we don't have a payment less than 0.01
                $paymentAmount = max(0.01, round($paymentAmount, 2));

                // Don't exceed the remaining amount
                $paymentAmount   = min($paymentAmount, $remainingAmount);
                $remainingAmount = $invoice->invoice_total - ($invoice->paid_amount + $paymentAmount);

                $paymentDate   = $invoice->invoiced_at->addDays(random_int(0, 60));
                $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];
                $reference     = mb_strtoupper(mb_substr($paymentMethod, 0, 3)) . '-' . rand(1000, 9999);

                // Debug log
                Log::info(sprintf(
                    'Creating payment for invoice %s: amount=%.2f, remaining=%.2f',
                    $invoice->invoice_number,
                    $paymentAmount,
                    $remainingAmount
                ));

                Payment::factory()
                    ->for($invoice->company)
                    ->for($invoice->customer, 'customer')
                    ->for($invoice, 'invoice')
                    ->create([
                        'payment_reference' => $reference,
                        'payment_method'    => $paymentMethod,
                        'payment_status'    => PaymentStatus::COMPLETED->value,
                        'paid_at'           => $paymentDate,
                        'payment_amount'    => $paymentAmount,
                        'notes'             => $this->getRandomNotes(),
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
