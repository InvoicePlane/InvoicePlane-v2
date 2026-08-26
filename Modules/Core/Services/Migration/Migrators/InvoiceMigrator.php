<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Throwable;

class InvoiceMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'invoices';
    }

    public function label(): string
    {
        return 'Invoices & Items';
    }

    public function inspect(MigrationContext $context): array
    {
        $invoices    = $context->getSourceTable('invoices');
        $items       = $context->getSourceTable('invoice_items');
        $notes       = [];
        $willMigrate = 0;
        $unmappable  = 0;

        foreach ($invoices as $row) {
            $clientId = $row['client_id'] ?? null;
            if ( ! $clientId) {
                $unmappable++;
                $notes[] = "Invoice #{$row['invoice_number']} has no client_id, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $invoices->count(),
            'will_migrate' => $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $invoices = $context->getSourceTable('invoices');
        $items    = $context->getSourceTable('invoice_items')->groupBy('invoice_id');
        $amounts  = $context->getSourceTable('invoice_amounts')->keyBy('invoice_id');
        $taxRates = $context->getSourceTable('invoice_tax_rates')->groupBy('invoice_id');

        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($invoices as $row) {
            $v1Id          = $row['invoice_id'] ?? null;
            $v1ClientId    = $row['client_id'] ?? null;
            $invoiceNumber = mb_trim((string) ($row['invoice_number'] ?? ''));

            $customerId = $context->getId('clients', $v1ClientId);
            if ( ! $customerId) {
                $errors[] = "Invoice #{$invoiceNumber} skipped: client #{$v1ClientId} not found in target company.";
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('invoices', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $v1Amount = $amounts[$v1Id] ?? [];
                $status   = $this->resolveStatus($row, $v1Amount);

                $itemSubtotal = (float) ($v1Amount['invoice_item_subtotal'] ?? 0.0);
                $itemTaxTotal = (float) ($v1Amount['invoice_item_tax_total'] ?? 0.0);
                $taxTotal     = (float) ($v1Amount['invoice_tax_total'] ?? 0.0);
                $total        = (float) ($v1Amount['invoice_total'] ?? 0.0);

                // Prevent duplicate invoice number in same company
                $invoice = Invoice::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('invoice_number', $invoiceNumber)
                    ->first();

                if ( ! $invoice) {
                    $invoice = Invoice::create([
                        'company_id'               => $context->getCompanyId(),
                        'customer_id'              => $customerId,
                        'user_id'                  => $context->getUserId(),
                        'invoice_number'           => $invoiceNumber ?: ('INV-' . $v1Id),
                        'invoice_status'           => $status,
                        'invoice_sign'             => (string) ($v1Amount['invoice_sign'] ?? '1'),
                        'invoiced_at'              => ! empty($row['invoice_date_created']) ? $row['invoice_date_created'] : now(),
                        'invoice_due_at'           => ! empty($row['invoice_date_due']) ? $row['invoice_date_due'] : now()->addDays(30),
                        'invoice_discount_amount'  => (float) ($row['invoice_discount_amount'] ?? 0.0),
                        'invoice_discount_percent' => (float) ($row['invoice_discount_percent'] ?? 0.0),
                        'invoice_item_subtotal'    => $itemSubtotal,
                        'item_tax_total'           => $itemTaxTotal,
                        'invoice_tax_total'        => $taxTotal,
                        'invoice_total'            => $total,
                        'invoice_password'         => ! empty($row['invoice_password']) ? (string) $row['invoice_password'] : null,
                        'url_key'                  => ! empty($row['invoice_url_key']) ? (string) $row['invoice_url_key'] : null,
                        'is_read_only'             => (bool) ($row['is_read_only'] ?? false),
                        'terms'                    => ! empty($row['invoice_terms']) ? (string) $row['invoice_terms'] : null,
                    ]);
                    $context->recordCreated(Invoice::class, $invoice->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('invoices', $v1Id, $invoice->id);
                }

                // Migrate invoice items
                $invoiceItems = $items[$v1Id] ?? collect();
                foreach ($invoiceItems as $itemRow) {
                    $taxRateId = $context->getId('tax_rates', $itemRow['item_tax_rate_id'] ?? null);
                    $productId = $context->getId('products', $itemRow['item_product_id'] ?? null);
                    $unitId    = $context->getId('product_units', $itemRow['item_product_unit_id'] ?? null);

                    $qty       = (float) ($itemRow['item_quantity'] ?? 1.0);
                    $price     = (float) ($itemRow['item_price'] ?? 0.0);
                    $discount  = (float) ($itemRow['item_discount_amount'] ?? 0.0);
                    $subtotal  = (float) ($itemRow['item_subtotal'] ?? ($qty * $price));
                    $itemTax   = (float) ($itemRow['item_tax_total'] ?? 0.0);
                    $itemTotal = (float) ($itemRow['item_total'] ?? ($subtotal + $itemTax));

                    $item = InvoiceItem::create([
                        'company_id'      => $context->getCompanyId(),
                        'invoice_id'      => $invoice->id,
                        'product_id'      => $productId,
                        'product_unit_id' => $unitId,
                        'tax_rate_id'     => $taxRateId,
                        'item_name'       => ! empty($itemRow['item_name']) ? (string) $itemRow['item_name'] : 'Item',
                        'description'     => ! empty($itemRow['item_description']) ? (string) $itemRow['item_description'] : null,
                        'quantity'        => $qty,
                        'price'           => $price,
                        'discount'        => $discount,
                        'subtotal'        => $subtotal,
                        'tax_1'           => $itemTax,
                        'tax_total'       => $itemTax,
                        'total'           => $itemTotal,
                        'display_order'   => (int) ($itemRow['item_order'] ?? 1),
                        'added_at'        => ! empty($itemRow['item_date_added']) ? $itemRow['item_date_added'] : $invoice->invoiced_at,
                    ]);
                    $context->recordCreated(InvoiceItem::class, $item->id);
                }

                // Migrate invoice tax rates pivot if applicable
                $invoiceTaxRows = $taxRates[$v1Id] ?? collect();
                foreach ($invoiceTaxRows as $taxRow) {
                    $taxRateId = $context->getId('tax_rates', $taxRow['tax_rate_id'] ?? null);
                    if ($taxRateId) {
                        $invoice->taxRates()->syncWithoutDetaching([
                            $taxRateId => [
                                'include_item_tax' => (int) ($taxRow['include_item_tax'] ?? 0),
                                'tax_total'        => (float) ($taxRow['invoice_tax_rate_amount'] ?? 0.0),
                            ],
                        ]);
                    }
                }

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate invoice #{$invoiceNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} invoices ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $itemIds    = $context->getCreatedIds(InvoiceItem::class);
        $invoiceIds = $context->getCreatedIds(Invoice::class);

        if ( ! empty($itemIds)) {
            InvoiceItem::withoutGlobalScopes()->whereIn('id', $itemIds)->delete();
        }

        if (empty($invoiceIds)) {
            return 0;
        }

        return Invoice::withoutGlobalScopes()
            ->whereIn('id', $invoiceIds)
            ->where('company_id', $context->getCompanyId())
            ->forceDelete();
    }

    protected function resolveStatus(array $row, array $amount): InvoiceStatus
    {
        $statusId = (int) ($row['invoice_status_id'] ?? 1);
        $balance  = (float) ($amount['invoice_balance'] ?? 0.0);
        $total    = (float) ($amount['invoice_total'] ?? 0.0);
        $paid     = (float) ($amount['invoice_paid'] ?? 0.0);
        $dueDate  = ! empty($row['invoice_date_due']) ? $row['invoice_date_due'] : null;

        if ($statusId === 4 || ($total > 0 && $balance <= 0.0001)) {
            return InvoiceStatus::PAID;
        }

        if ($statusId === 1) {
            return InvoiceStatus::DRAFT;
        }

        if ($paid > 0 && $balance > 0.0001) {
            return InvoiceStatus::PARTIALLY_PAID;
        }

        if ($dueDate && strtotime($dueDate) < time() && $balance > 0.0001) {
            return InvoiceStatus::OVERDUE;
        }

        return match ($statusId) {
            2       => InvoiceStatus::SENT,
            3       => InvoiceStatus::VIEWED,
            default => InvoiceStatus::DRAFT,
        };
    }
}
