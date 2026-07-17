<?php

namespace Modules\Invoices\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Clients\Enums\CommunicationType;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Mail\InvoiceMailable;
use Modules\Invoices\Models\Invoice;
use RuntimeException;
use Throwable;

class InvoiceService extends BaseService
{
    private const DEFAULT_INVOICE_EMAIL_SUBJECT = 'New Invoice: {{ invoice.number }}';

    private const DEFAULT_INVOICE_EMAIL_BODY = "Dear {{ customer.name }},\n\n"
        . "A new invoice {{ invoice.number }} has been created for you.\n\n"
        . "Amount Due: {{ invoice.total_formatted }}\n"
        . "Due Date: {{ invoice.due_date_formatted }}\n\n"
        . "Thank you for your business!\n\n"
        . '{{ company.name }}';

    public function model(): string
    {
        return Invoice::class;
    }

    public function createInvoice(array $data): Invoice
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal    = $this->calculateItemTaxTotal($data);
            $invoiceTaxTotal = $this->calculateInvoiceTaxTotal($data);
            $invoiceTotal    = $this->calculateInvoiceTotal($data, $itemTaxTotal, $invoiceTaxTotal);

            $invoice = Invoice::query()->create([
                'customer_id'              => $data['customer_id'],
                'numbering_id'             => $data['numbering_id'] ?? null,
                'creditinvoice_parent_id'  => $data['creditinvoice_parent_id'] ?? null,
                'user_id'                  => auth()->id(),
                'invoice_number'           => $data['invoice_number'],
                'invoice_status'           => $data['invoice_status'],
                'invoice_sign'             => $data['invoice_sign'] ?? '1',
                'invoiced_at'              => Carbon::parse($data['invoiced_at']),
                'invoice_due_at'           => Carbon::parse($data['invoice_due_at']),
                'invoice_discount_amount'  => $data['invoice_discount_amount'] ?? 0,
                'invoice_discount_percent' => $data['invoice_discount_percent'] ?? 0,
                'item_tax_total'           => $itemTaxTotal,
                'invoice_item_subtotal'    => $data['invoice_item_subtotal'],
                'invoice_tax_total'        => $invoiceTaxTotal,
                'invoice_total'            => $invoiceTotal,
                'invoice_password'         => $data['invoice_password'] ?? null,
                'url_key'                  => $data['url_key'] ?? Str::random(32),
                'is_read_only'             => $data['is_read_only'] ?? false,
                'template'                 => $data['template'] ?? null,
                'summary'                  => $data['summary'] ?? null,
                'terms'                    => $data['terms'] ?? null,
                'footer'                   => $data['footer'] ?? null,
            ]);

            foreach ($data['invoiceItems'] ?? [] as $item) {
                $invoice->invoiceItems()->create([
                    'product_id'      => $item['product_id'] ?? null,
                    'product_unit_id' => $item['product_unit_id'] ?? null,
                    'item_name'       => $item['item_name'] ?? null,
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                    'discount'        => $item['discount'] ?? 0,
                    'subtotal'        => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                    'tax_1'           => $item['tax_1'] ?? 0,
                    'tax_2'           => $item['tax_2'] ?? 0,
                    'tax_total'       => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0),
                    'total'           => $item['total'] ?? 0,
                    'description'     => $item['description'] ?? null,
                    'tax_rate_id'     => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id'   => $item['tax_rate_2_id'] ?? null,
                    'display_order'   => $item['display_order'] ?? null,
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal    = $this->calculateItemTaxTotal($data);
            $invoiceTaxTotal = $this->calculateInvoiceTaxTotal($data);
            $invoiceTotal    = $this->calculateInvoiceTotal($data, $itemTaxTotal, $invoiceTaxTotal);

            $invoice->update([
                'customer_id'              => $data['customer_id'],
                'numbering_id'             => $data['numbering_id'] ?? null,
                'creditinvoice_parent_id'  => $data['creditinvoice_parent_id'] ?? null,
                'user_id'                  => auth()->id(),
                'invoice_number'           => $data['invoice_number'],
                'invoice_status'           => $data['invoice_status'],
                'invoice_sign'             => $data['invoice_sign'] ?? '1',
                'invoiced_at'              => Carbon::parse($data['invoiced_at']),
                'invoice_due_at'           => Carbon::parse($data['invoice_due_at']),
                'invoice_discount_amount'  => $data['invoice_discount_amount'] ?? 0,
                'invoice_discount_percent' => $data['invoice_discount_percent'] ?? 0,
                'item_tax_total'           => $itemTaxTotal,
                'invoice_item_subtotal'    => $data['invoice_item_subtotal'],
                'invoice_tax_total'        => $invoiceTaxTotal,
                'invoice_total'            => $invoiceTotal,
                'invoice_password'         => $data['invoice_password'] ?? null,
                'url_key'                  => $data['url_key'] ?? Str::random(32),
                'is_read_only'             => $data['is_read_only'] ?? false,
                'template'                 => $data['template'] ?? null,
                'summary'                  => $data['summary'] ?? null,
                'terms'                    => $data['terms'] ?? null,
                'footer'                   => $data['footer'] ?? null,
            ]);

            $existingItems = $invoice->invoiceItems()->get()->keyBy('id');
            $incomingItems = collect($data['invoiceItems'] ?? []);

            $incomingItems->each(function ($item) use ($existingItems, $invoice) {
                if (isset($item['_delete']) && $item['_delete']) {
                    if (isset($item['id']) && $existingItems->has($item['id'])) {
                        $existingItems->get($item['id'])->delete();
                    }

                    return;
                }

                if (isset($item['id']) && $existingItems->has($item['id'])) {
                    $existingItems->get($item['id'])->update([
                        'product_id'      => $item['product_id'] ?? null,
                        'product_unit_id' => $item['product_unit_id'] ?? null,
                        'item_name'       => $item['item_name'] ?? null,
                        'quantity'        => $item['quantity'],
                        'price'           => $item['price'],
                        'discount'        => $item['discount'] ?? 0,
                        'subtotal'        => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                        'tax_1'           => $item['tax_1'] ?? 0,
                        'tax_2'           => $item['tax_2'] ?? 0,
                        'tax_total'       => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0),
                        'total'           => $item['total'] ?? 0,
                        'description'     => $item['description'] ?? null,
                        'tax_rate_id'     => $item['tax_rate_id'] ?? null,
                        'tax_rate_2_id'   => $item['tax_rate_2_id'] ?? null,
                        'display_order'   => $item['display_order'] ?? null,
                    ]);
                } else {
                    $invoice->invoiceItems()->create([
                        'product_id'      => $item['product_id'] ?? null,
                        'product_unit_id' => $item['product_unit_id'] ?? null,
                        'item_name'       => $item['item_name'] ?? null,
                        'quantity'        => $item['quantity'],
                        'price'           => $item['price'],
                        'discount'        => $item['discount'] ?? 0,
                        'subtotal'        => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                        'tax_1'           => $item['tax_1'] ?? 0,
                        'tax_2'           => $item['tax_2'] ?? 0,
                        'tax_total'       => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0),
                        'total'           => $item['total'] ?? 0,
                        'description'     => $item['description'] ?? null,
                        'tax_rate_id'     => $item['tax_rate_id'] ?? null,
                        'tax_rate_2_id'   => $item['tax_rate_2_id'] ?? null,
                        'display_order'   => $item['display_order'] ?? null,
                    ]);
                }
            });

            $incomingIds = $incomingItems->pluck('id')->filter()->all();
            $existingItems->whereNotIn('id', $incomingIds)->each->delete();

            DB::commit();

            return $invoice;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteInvoice(Invoice $invoice): Invoice
    {
        if ($invoice->invoice_status === InvoiceStatus::PAID) {
            throw new InvalidArgumentException(trans('ip.cannot_delete_paid_invoice'));
        }

        DB::beginTransaction();
        try {
            $invoice->invoiceItems()->delete();
            $invoice->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $invoice;
    }

    /**
     * Resolve the customer's primary contact email, render the invoice
     * EmailTemplate (falling back to a default), and queue the invoice
     * mailable for delivery.
     *
     * @throws RuntimeException when no recipient email address can be resolved
     */
    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $recipientEmail = $this->resolveInvoiceRecipientEmail($invoice);

        if ($recipientEmail === null) {
            throw new RuntimeException(trans('ip.invoice_email_no_recipient'));
        }

        $template = EmailTemplate::query()
            ->where('title', 'invoice_sent')
            ->first();

        $subjectTemplate = $template?->subject ?: self::DEFAULT_INVOICE_EMAIL_SUBJECT;
        $bodyTemplate    = $template?->body ?: self::DEFAULT_INVOICE_EMAIL_BODY;

        $subject = $this->renderInvoiceEmailTemplate($subjectTemplate, $invoice);
        $body    = $this->renderInvoiceEmailTemplate($bodyTemplate, $invoice);

        Mail::to($recipientEmail)->queue(new InvoiceMailable($invoice, $subject, $body));
    }

    /**
     * Walk the invoice's customer → contacts → communications chain and
     * return the first email address found, preferring a primary one.
     */
    private function resolveInvoiceRecipientEmail(Invoice $invoice): ?string
    {
        $invoice->loadMissing('customer.contacts.communications');

        $customer = $invoice->customer;

        if ( ! $customer) {
            return null;
        }

        $emailCommunication = $customer->contacts
            ->flatMap(fn ($contact) => $contact->communications)
            ->filter(fn ($communication) => $communication->communication_type === CommunicationType::EMAIL->value)
            ->sortByDesc('is_primary')
            ->first();

        return $emailCommunication?->communication_value;
    }

    /**
     * Replace the invoice's mini-templating placeholders with real values.
     */
    private function renderInvoiceEmailTemplate(string $template, Invoice $invoice): string
    {
        $replacements = [
            '{{ customer.name }}'             => $invoice->customer?->company_name ?? '',
            '{{ invoice.number }}'             => $invoice->invoice_number ?? '',
            '{{ invoice.total_formatted }}'    => number_format((float) $invoice->invoice_total, 2),
            '{{ invoice.due_date_formatted }}' => $invoice->invoice_due_at?->format('Y-m-d') ?? '',
            '{{ company.name }}'               => $invoice->company?->name ?? '',
        ];

        return strtr($template, $replacements);
    }

    private function calculateItemTaxTotal(array $data): float
    {
        return collect($data['invoiceItems'] ?? [])->sum(fn ($item) => $item['tax'] ?? 0);
    }

    private function calculateInvoiceTaxTotal(array $data): float
    {
        return collect($data['invoiceItems'] ?? [])->sum(fn ($item) => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0));
    }

    private function calculateInvoiceTotal(array $data, float $itemTaxTotal, float $invoiceTaxTotal): float
    {
        $subtotal       = $data['invoice_item_subtotal'] ?? 0;
        $discountAmount = $data['invoice_discount_amount'] ?? 0;

        return $subtotal + $itemTaxTotal + $invoiceTaxTotal - $discountAmount;
    }
}
