<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use Throwable;

class QuoteMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'quotes';
    }

    public function label(): string
    {
        return 'Quotes & Items';
    }

    public function inspect(MigrationContext $context): array
    {
        $quotes      = $context->getSourceTable('quotes');
        $notes       = [];
        $willMigrate = 0;
        $unmappable  = 0;

        foreach ($quotes as $row) {
            $clientId = $row['client_id'] ?? null;
            if ( ! $clientId) {
                $unmappable++;
                $notes[] = "Quote #{$row['quote_number']} has no client_id, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $quotes->count(),
            'will_migrate' => $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $quotes  = $context->getSourceTable('quotes');
        $items   = $context->getSourceTable('quote_items')->groupBy('quote_id');
        $amounts = $context->getSourceTable('quote_amounts')->keyBy('quote_id');

        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($quotes as $row) {
            $v1Id        = $row['quote_id'] ?? null;
            $v1ClientId  = $row['client_id'] ?? null;
            $quoteNumber = mb_trim((string) ($row['quote_number'] ?? ''));

            $prospectId = $context->getId('clients', $v1ClientId);
            if ( ! $prospectId) {
                $errors[] = "Quote #{$quoteNumber} skipped: client #{$v1ClientId} not found in target company.";
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('quotes', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $v1Amount = $amounts[$v1Id] ?? [];
                $status   = $this->resolveStatus($row);

                $itemSubtotal = (float) ($v1Amount['quote_item_subtotal'] ?? 0.0);
                $itemTaxTotal = (float) ($v1Amount['quote_item_tax_total'] ?? 0.0);
                $taxTotal     = (float) ($v1Amount['quote_tax_total'] ?? 0.0);
                $total        = (float) ($v1Amount['quote_total'] ?? 0.0);

                $quote = Quote::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('quote_number', $quoteNumber)
                    ->first();

                if ( ! $quote) {
                    $quote = Quote::create([
                        'company_id'             => $context->getCompanyId(),
                        'prospect_id'            => $prospectId,
                        'user_id'                => $context->getUserId(),
                        'quote_number'           => $quoteNumber ?: ('QUO-' . $v1Id),
                        'quote_status'           => $status,
                        'quoted_at'              => ! empty($row['quote_date_created']) ? $row['quote_date_created'] : now(),
                        'quote_expires_at'       => ! empty($row['quote_date_expires']) ? $row['quote_date_expires'] : now()->addDays(30),
                        'quote_discount_amount'  => (float) ($row['quote_discount_amount'] ?? 0.0),
                        'quote_discount_percent' => (float) ($row['quote_discount_percent'] ?? 0.0),
                        'quote_item_subtotal'    => $itemSubtotal,
                        'item_tax_total'         => $itemTaxTotal,
                        'quote_tax_total'        => $taxTotal,
                        'quote_total'            => $total,
                        'quote_password'         => ! empty($row['quote_password']) ? (string) $row['quote_password'] : null,
                        'url_key'                => ! empty($row['quote_url_key']) ? (string) $row['quote_url_key'] : null,
                    ]);
                    $context->recordCreated(Quote::class, $quote->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('quotes', $v1Id, $quote->id);
                }

                // Migrate quote items
                $quoteItems = $items[$v1Id] ?? collect();
                foreach ($quoteItems as $itemRow) {
                    $taxRateId = $context->getId('tax_rates', $itemRow['item_tax_rate_id'] ?? null);
                    $productId = $context->getId('products', $itemRow['item_product_id'] ?? null);

                    $qty       = (float) ($itemRow['item_quantity'] ?? 1.0);
                    $price     = (float) ($itemRow['item_price'] ?? 0.0);
                    $discount  = (float) ($itemRow['item_discount_amount'] ?? 0.0);
                    $subtotal  = (float) ($itemRow['item_subtotal'] ?? ($qty * $price));
                    $itemTax   = (float) ($itemRow['item_tax_total'] ?? 0.0);
                    $itemTotal = (float) ($itemRow['item_total'] ?? ($subtotal + $itemTax));

                    $item = QuoteItem::create([
                        'company_id'    => $context->getCompanyId(),
                        'quote_id'      => $quote->id,
                        'product_id'    => $productId,
                        'tax_rate_id'   => $taxRateId,
                        'item_name'     => ! empty($itemRow['item_name']) ? (string) $itemRow['item_name'] : 'Item',
                        'description'   => ! empty($itemRow['item_description']) ? (string) $itemRow['item_description'] : null,
                        'quantity'      => $qty,
                        'price'         => $price,
                        'discount'      => $discount,
                        'subtotal'      => $subtotal,
                        'tax_1'         => $itemTax,
                        'tax_total'     => $itemTax,
                        'total'         => $itemTotal,
                        'display_order' => (int) ($itemRow['item_order'] ?? 1),
                        'added_at'      => ! empty($itemRow['item_date_added']) ? $itemRow['item_date_added'] : $quote->quoted_at,
                    ]);
                    $context->recordCreated(QuoteItem::class, $item->id);
                }

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate quote #{$quoteNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} quotes ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $itemIds  = $context->getCreatedIds(QuoteItem::class);
        $quoteIds = $context->getCreatedIds(Quote::class);

        if ( ! empty($itemIds)) {
            QuoteItem::withoutGlobalScopes()->whereIn('id', $itemIds)->delete();
        }

        if (empty($quoteIds)) {
            return 0;
        }

        return Quote::withoutGlobalScopes()
            ->whereIn('id', $quoteIds)
            ->where('company_id', $context->getCompanyId())
            ->forceDelete();
    }

    protected function resolveStatus(array $row): QuoteStatus
    {
        $statusId  = (int) ($row['quote_status_id'] ?? 1);
        $invoiceId = $row['invoice_id'] ?? null;

        if ( ! empty($invoiceId)) {
            return QuoteStatus::CONVERTED;
        }

        return match ($statusId) {
            2       => QuoteStatus::SENT,
            3       => QuoteStatus::VIEWED,
            4       => QuoteStatus::APPROVED,
            5       => QuoteStatus::REJECTED,
            6       => QuoteStatus::CONVERTED,
            default => QuoteStatus::DRAFT,
        };
    }
}
