<?php

namespace Modules\Invoices\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Support\EmailTemplatePreview;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InvoiceService extends BaseService
{
    /**
     * Title of the EmailTemplate used as the company's invoice email template.
     */
    public const INVOICE_EMAIL_TEMPLATE_TITLE = 'invoice_sent';

    public function model(): string
    {
        return Invoice::class;
    }

    /**
     * Resolve the recipient/subject/body defaults for the "Email Invoice" modal,
     * rendering the company's invoice email template against this invoice.
     */
    public function resolveEmailDefaults(Invoice $invoice): array
    {
        $invoice->loadMissing(['customer', 'company']);

        $template = EmailTemplate::forCompany($invoice->company_id)
            ->where('title', self::INVOICE_EMAIL_TEMPLATE_TITLE)
            ->first();

        $placeholders = [
            'invoice.number'             => $invoice->invoice_number,
            'invoice.total_formatted'    => number_format((float) $invoice->invoice_total, 2),
            'invoice.due_date_formatted' => DateHelpers::formatDate($invoice->invoice_due_at),
            'customer.name'              => $invoice->customer?->company_name,
            'company.name'               => $invoice->company?->name,
        ];

        $defaultSubject = trans('ip.email_invoice_default_subject', ['number' => $invoice->invoice_number]);

        return [
            'recipient' => $invoice->customer?->customer_email,
            'subject'   => $template?->subject
                ? EmailTemplatePreview::render($template->subject, $placeholders)
                : $defaultSubject,
            'body' => $template?->body
                ? EmailTemplatePreview::render($template->body, $placeholders)
                : '',
        ];
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
     * Render the invoice document markup used by both the PDF driver and
     * the on-screen preview.
     */
    public function renderHtml(Invoice $invoice): string
    {
        $invoice->loadMissing(['company', 'customer', 'invoiceItems']);

        return view('invoices::pdf.invoice', ['invoice' => $invoice])->render();
    }

    /**
     * Stream the invoice as a PDF download named after the invoice number.
     */
    public function generatePdf(Invoice $invoice): StreamedResponse
    {
        $driver   = PDFFactory::create();
        $output   = $driver->getOutput($this->renderHtml($invoice));
        $filename = ($invoice->invoice_number ?: 'invoice-draft-' . $invoice->id) . '.pdf';

        return response()->streamDownload(
            function () use ($output): void {
                echo $output;
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Issue a credit note for a sent/paid invoice: a mirrored draft invoice
     * with negated amounts linked via creditinvoice_parent_id. The credit
     * note may share the parent's number (the duplicate-number guard allows
     * this), but starts unnumbered like every draft.
     */
    public function createCreditNote(Invoice $invoice): Invoice
    {
        if ($invoice->creditinvoice_parent_id !== null) {
            throw new InvalidArgumentException(trans('ip.cannot_credit_a_credit_note'));
        }

        return DB::transaction(function () use ($invoice) {
            $creditNote = Invoice::query()->create([
                'company_id'               => $invoice->company_id,
                'customer_id'              => $invoice->customer_id,
                'numbering_id'             => $invoice->numbering_id,
                'creditinvoice_parent_id'  => $invoice->id,
                'user_id'                  => auth()->id() ?? $invoice->user_id,
                'invoice_number'           => null,
                'invoice_status'           => InvoiceStatus::DRAFT->value,
                'invoice_sign'             => '-1',
                'invoiced_at'              => Carbon::today(),
                'invoice_due_at'           => Carbon::today()->addDays(30),
                'invoice_discount_amount'  => -1 * (float) $invoice->invoice_discount_amount,
                'invoice_discount_percent' => $invoice->invoice_discount_percent,
                'item_tax_total'           => -1 * (float) $invoice->item_tax_total,
                'invoice_item_subtotal'    => -1 * (float) $invoice->invoice_item_subtotal,
                'invoice_tax_total'        => -1 * (float) $invoice->invoice_tax_total,
                'invoice_total'            => -1 * (float) $invoice->invoice_total,
                'url_key'                  => Str::random(32),
                'summary'                  => $invoice->summary,
                'terms'                    => $invoice->terms,
                'footer'                   => $invoice->footer,
            ]);

            foreach ($invoice->invoiceItems as $item) {
                $creditNote->invoiceItems()->create([
                    'company_id'      => $item->company_id,
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'task_id'         => $item->task_id,
                    'added_at'        => Carbon::today()->toDateString(),
                    'item_name'       => $item->item_name,
                    'description'     => $item->description,
                    'quantity'        => $item->quantity,
                    'price'           => -1 * (float) $item->price,
                    'discount'        => $item->discount,
                    'subtotal'        => -1 * (float) $item->subtotal,
                    'tax_1'           => -1 * (float) $item->tax_1,
                    'tax_2'           => -1 * (float) $item->tax_2,
                    'tax_total'       => -1 * (float) $item->tax_total,
                    'total'           => -1 * (float) $item->total,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                ]);
            }

            return $creditNote;
        });
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
