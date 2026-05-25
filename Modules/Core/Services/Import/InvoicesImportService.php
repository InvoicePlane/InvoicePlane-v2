<?php

namespace Modules\Core\Services\Import;

use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

class InvoicesImportService extends AbstractImportService
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getTables(): array
    {
        return ['ip_invoices', 'ip_invoice_items'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['invoices', 'invoice_items']);

        $this->importInvoices();

        return $this->stats;
    }

    private function importInvoices(): void
    {
        $invoices = $this->getImportData('ip_invoices');
        $allItems = collect($this->getImportData('ip_invoice_items'))->groupBy('invoice_id');

        foreach ($invoices as $v1Invoice) {
            $customerId = $this->idMappings['clients'][$v1Invoice->client_id] ?? null;
            $numberingId = $this->idMappings['invoice_groups'][$v1Invoice->invoice_group_id] ?? null;

            if (! $customerId) {
                continue;
            }

            $invoice = Invoice::create([
                'company_id'               => $this->companyId,
                'customer_id'              => $customerId,
                'numbering_id'             => $numberingId,
                'user_id'                  => $this->userId,
                'invoice_number'           => $v1Invoice->invoice_number,
                'invoice_status'           => $this->mapInvoiceStatus($v1Invoice->invoice_status_id ?? 1)->value,
                'invoiced_at'              => $v1Invoice->invoice_date_created ?? now(),
                'invoice_due_at'           => $v1Invoice->invoice_date_due ?? now()->addDays(30),
                'invoice_discount_percent' => $v1Invoice->invoice_discount_percent ?? 0,
                'invoice_discount_amount'  => $v1Invoice->invoice_discount_amount ?? 0,
                'item_tax_total'           => $v1Invoice->invoice_item_tax_total ?? 0,
                'invoice_item_subtotal'    => $v1Invoice->invoice_item_subtotal ?? 0,
                'invoice_tax_total'        => $v1Invoice->invoice_tax_total ?? 0,
                'invoice_total'            => $v1Invoice->invoice_total ?? 0,
                'url_key'                  => $v1Invoice->invoice_url_key ?? null,
                'terms'                    => $v1Invoice->invoice_terms ?? null,
            ]);

            $this->idMappings['invoices'][$v1Invoice->invoice_id] = $invoice->id;
            $this->stats['invoices']++;

            $this->importInvoiceItems($allItems->get($v1Invoice->invoice_id, collect()), $invoice->id);
        }
    }

    private function importInvoiceItems($v1Items, int $v2InvoiceId): void
    {
        foreach ($v1Items as $v1Item) {
            $productId = $this->idMappings['products'][$v1Item->item_product_id] ?? null;
            $taxRateId = $this->idMappings['tax_rates'][$v1Item->item_tax_rate_id] ?? null;

            InvoiceItem::create([
                'company_id'      => $this->companyId,
                'invoice_id'      => $v2InvoiceId,
                'product_id'      => $productId,
                'item_name'       => $v1Item->item_name ?? 'Item',
                'quantity'        => $v1Item->item_quantity ?? 1,
                'price'           => $v1Item->item_price ?? 0,
                'discount'        => $v1Item->item_discount_amount ?? 0,
                'tax_rate_id'     => $taxRateId,
                'subtotal'        => $v1Item->item_subtotal ?? 0,
                'tax_total'       => $v1Item->item_tax_total ?? 0,
                'total'           => $v1Item->item_total ?? 0,
                'description'     => $v1Item->item_description ?? null,
                'display_order'   => $v1Item->item_order ?? 0,
            ]);

            $this->stats['invoice_items']++;
        }
    }

    private function mapInvoiceStatus(int $statusId): InvoiceStatus
    {
        return match ($statusId) {
            1       => InvoiceStatus::DRAFT,
            2       => InvoiceStatus::SENT,
            3       => InvoiceStatus::VIEWED,
            4       => InvoiceStatus::PAID,
            5       => InvoiceStatus::OVERDUE,
            default => InvoiceStatus::DRAFT,
        };
    }
}
