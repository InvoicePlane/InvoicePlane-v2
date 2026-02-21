<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class ImportInvoicePlaneV1Service
{
    private const TEMP_DB_NAME = 'invoiceplane_v1_temp';

    private ?int $companyId = null;

    private array $idMappings = [
        'clients'           => [],
        'products'          => [],
        'product_families'  => [],
        'product_units'     => [],
        'invoice_groups'    => [],
        'invoices'          => [],
        'quotes'            => [],
        'tax_rates'         => [],
    ];

    private array $stats = [
        'product_categories' => 0,
        'product_units'      => 0,
        'products'           => 0,
        'clients'            => 0,
        'invoice_groups'     => 0,
        'invoices'           => 0,
        'invoice_items'      => 0,
        'quotes'             => 0,
        'quote_items'        => 0,
        'payments'           => 0,
    ];

    /**
     * Import InvoicePlane v1 data from a mysqldump file
     */
    public function import(string $dumpFile, ?int $companyId = null): array
    {
        // Step 1: Setup company
        $this->companyId = $companyId ?? $this->createCompany();

        // Step 2: Create temporary database and restore dump
        $this->createTemporaryDatabase();
        $this->restoreDump($dumpFile);

        try {
            // Step 3: Import data in dependency order
            $this->importTaxRates();
            $this->importProductFamilies();
            $this->importProductUnits();
            $this->importProducts();
            $this->importClients();
            $this->importInvoiceGroups();
            $this->importInvoices();
            $this->importQuotes();
            $this->importPayments();

            return $this->stats;
        } finally {
            // Step 4: Cleanup temporary database
            $this->dropTemporaryDatabase();
        }
    }

    /**
     * Create a new company for import
     */
    private function createCompany(): int
    {
        $company = Company::create([
            'company_name' => 'Imported from InvoicePlane v1',
            'subdomain'    => 'imported-' . uniqid(),
        ]);

        return $company->id;
    }

    /**
     * Create temporary database for import
     */
    private function createTemporaryDatabase(): void
    {
        DB::statement('DROP DATABASE IF EXISTS ' . self::TEMP_DB_NAME);
        DB::statement('CREATE DATABASE ' . self::TEMP_DB_NAME);
    }

    /**
     * Restore mysqldump to temporary database
     */
    private function restoreDump(string $dumpFile): void
    {
        $config = Config::get('database.connections.mysql');
        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];
        $port = $config['port'] ?? 3306;

        $passwordArg = $password ? "-p{$password}" : '';
        $command = "mysql -h{$host} -P{$port} -u{$username} {$passwordArg} " . self::TEMP_DB_NAME . " < {$dumpFile} 2>&1";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException('Failed to restore dump: ' . implode("\n", $output));
        }
    }

    /**
     * Drop temporary database
     */
    private function dropTemporaryDatabase(): void
    {
        DB::statement('DROP DATABASE IF EXISTS ' . self::TEMP_DB_NAME);
    }

    /**
     * Check if a table exists in the temporary database
     */
    private function tableExists(string $tableName): bool
    {
        try {
            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.tables 
                WHERE table_schema = ? AND table_name = ?",
                [self::TEMP_DB_NAME, $tableName]
            );

            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Import tax rates from v1
     */
    private function importTaxRates(): void
    {
        if (! $this->tableExists('ip_tax_rates')) {
            return;
        }

        $taxRates = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_tax_rates')
            ->get();

        foreach ($taxRates as $v1TaxRate) {
            $v2TaxRate = TaxRate::firstOrCreate(
                [
                    'company_id' => $this->companyId,
                    'tax_name'   => $v1TaxRate->tax_rate_name ?? 'Tax',
                    'tax_rate'   => $v1TaxRate->tax_rate_percent ?? 0,
                ],
            );

            $this->idMappings['tax_rates'][$v1TaxRate->tax_rate_id] = $v2TaxRate->id;
        }
    }

    /**
     * Import product families (categories) from v1
     */
    private function importProductFamilies(): void
    {
        if (! $this->tableExists('ip_families')) {
            return;
        }

        $families = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_families')
            ->get();

        foreach ($families as $family) {
            $category = ProductCategory::create([
                'company_id'    => $this->companyId,
                'category_name' => $family->family_name,
                'description'   => null,
            ]);

            $this->idMappings['product_families'][$family->family_id] = $category->id;
            $this->stats['product_categories']++;
        }
    }

    /**
     * Import product units from v1
     */
    private function importProductUnits(): void
    {
        if (! $this->tableExists('ip_units')) {
            return;
        }

        $units = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_units')
            ->get();

        foreach ($units as $unit) {
            $productUnit = ProductUnit::create([
                'company_id'     => $this->companyId,
                'unit_name'      => $unit->unit_name,
                'unit_name_plrl' => $unit->unit_name_plrl ?? $unit->unit_name,
            ]);

            $this->idMappings['product_units'][$unit->unit_id] = $productUnit->id;
            $this->stats['product_units']++;
        }
    }

    /**
     * Import products from v1
     */
    private function importProducts(): void
    {
        if (! $this->tableExists('ip_products')) {
            return;
        }

        $products = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_products')
            ->get();

        foreach ($products as $v1Product) {
            $categoryId = $this->idMappings['product_families'][$v1Product->family_id] ?? null;
            $unitId = $this->idMappings['product_units'][$v1Product->unit_id] ?? null;
            $taxRateId = $this->idMappings['tax_rates'][$v1Product->tax_rate_id] ?? null;

            if (! $categoryId) {
                // Create default category if not found
                $defaultCategory = ProductCategory::firstOrCreate([
                    'company_id'    => $this->companyId,
                    'category_name' => 'Default',
                    'description'   => 'Default category for imported products',
                ]);
                $categoryId = $defaultCategory->id;
            }

            Product::create([
                'company_id'   => $this->companyId,
                'category_id'  => $categoryId,
                'unit_id'      => $unitId,
                'type'         => 'service', // Default to service
                'code'         => $v1Product->product_sku ?? null,
                'product_name' => $v1Product->product_name,
                'price'        => $v1Product->product_price ?? 0,
                'tax_rate_id'  => $taxRateId,
                'description'  => $v1Product->product_description ?? null,
            ]);

            $this->idMappings['products'][$v1Product->product_id] = Product::latest('id')->first()->id;
            $this->stats['products']++;
        }
    }

    /**
     * Import clients from v1
     */
    private function importClients(): void
    {
        if (! $this->tableExists('ip_clients')) {
            return;
        }

        $clients = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_clients')
            ->get();

        foreach ($clients as $v1Client) {
            $relation = Relation::create([
                'company_id'      => $this->companyId,
                'relation_type'   => 'customer',
                'relation_status' => $v1Client->client_active == 1 ? 'active' : 'inactive',
                'relation_number' => $v1Client->client_name ?? 'CLIENT-' . $v1Client->client_id,
                'company_name'    => $v1Client->client_name,
                'vat_number'      => $v1Client->client_vat_id ?? null,
                'registered_at'   => now(),
            ]);

            $this->idMappings['clients'][$v1Client->client_id] = $relation->id;
            $this->stats['clients']++;
        }
    }

    /**
     * Import invoice groups (numbering) from v1
     */
    private function importInvoiceGroups(): void
    {
        if (! $this->tableExists('ip_invoice_groups')) {
            return;
        }

        $groups = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_invoice_groups')
            ->get();

        foreach ($groups as $group) {
            $numbering = Numbering::create([
                'company_id' => $this->companyId,
                'type'       => 'invoice',
                'name'       => $group->invoice_group_name,
                'next_id'    => $group->invoice_group_next_id ?? 1,
                'left_pad'   => 0,
                'format'     => $group->invoice_group_prefix ?? 'INV',
                'prefix'     => $group->invoice_group_prefix ?? 'INV',
            ]);

            $this->idMappings['invoice_groups'][$group->invoice_group_id] = $numbering->id;
            $this->stats['invoice_groups']++;
        }
    }

    /**
     * Import invoices from v1
     */
    private function importInvoices(): void
    {
        if (! $this->tableExists('ip_invoices')) {
            return;
        }

        $invoices = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_invoices')
            ->get();

        foreach ($invoices as $v1Invoice) {
            $customerId = $this->idMappings['clients'][$v1Invoice->client_id] ?? null;
            $numberingId = $this->idMappings['invoice_groups'][$v1Invoice->invoice_group_id] ?? null;

            if (! $customerId) {
                continue; // Skip invoices without clients
            }

            $invoice = Invoice::create([
                'company_id'               => $this->companyId,
                'customer_id'              => $customerId,
                'numbering_id'             => $numberingId,
                'user_id'                  => 1, // Default user
                'invoice_number'           => $v1Invoice->invoice_number,
                'invoice_status'           => $this->mapInvoiceStatus($v1Invoice->invoice_status_id ?? 1),
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

            // Import invoice items
            $this->importInvoiceItems($v1Invoice->invoice_id, $invoice->id);
        }
    }

    /**
     * Import invoice items for a specific invoice
     */
    private function importInvoiceItems(int $v1InvoiceId, int $v2InvoiceId): void
    {
        if (! $this->tableExists('ip_invoice_items')) {
            return;
        }

        $items = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_invoice_items')
            ->where('invoice_id', $v1InvoiceId)
            ->get();

        foreach ($items as $v1Item) {
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

    /**
     * Import quotes from v1
     */
    private function importQuotes(): void
    {
        if (! $this->tableExists('ip_quotes')) {
            return;
        }

        $quotes = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_quotes')
            ->get();

        foreach ($quotes as $v1Quote) {
            $prospectId = $this->idMappings['clients'][$v1Quote->client_id] ?? null;
            $numberingId = $this->idMappings['invoice_groups'][$v1Quote->quote_group_id] ?? null;

            if (! $prospectId) {
                continue; // Skip quotes without clients
            }

            $quote = Quote::create([
                'company_id'             => $this->companyId,
                'prospect_id'            => $prospectId,
                'numbering_id'           => $numberingId,
                'user_id'                => 1, // Default user
                'quote_number'           => $v1Quote->quote_number,
                'quote_status'           => $this->mapQuoteStatus($v1Quote->quote_status_id ?? 1),
                'quoted_at'              => $v1Quote->quote_date_created ?? now(),
                'quote_expires_at'       => $v1Quote->quote_date_expires ?? now()->addDays(30),
                'quote_discount_percent' => $v1Quote->quote_discount_percent ?? 0,
                'quote_discount_amount'  => $v1Quote->quote_discount_amount ?? 0,
                'item_tax_total'         => $v1Quote->quote_item_tax_total ?? 0,
                'quote_item_subtotal'    => $v1Quote->quote_item_subtotal ?? 0,
                'quote_tax_total'        => $v1Quote->quote_tax_total ?? 0,
                'quote_total'            => $v1Quote->quote_total ?? 0,
                'url_key'                => $v1Quote->quote_url_key ?? null,
                'terms'                  => $v1Quote->quote_terms ?? null,
            ]);

            $this->idMappings['quotes'][$v1Quote->quote_id] = $quote->id;
            $this->stats['quotes']++;

            // Import quote items
            $this->importQuoteItems($v1Quote->quote_id, $quote->id);
        }
    }

    /**
     * Import quote items for a specific quote
     */
    private function importQuoteItems(int $v1QuoteId, int $v2QuoteId): void
    {
        if (! $this->tableExists('ip_quote_items')) {
            return;
        }

        $items = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_quote_items')
            ->where('quote_id', $v1QuoteId)
            ->get();

        foreach ($items as $v1Item) {
            $productId = $this->idMappings['products'][$v1Item->item_product_id] ?? null;
            $taxRateId = $this->idMappings['tax_rates'][$v1Item->item_tax_rate_id] ?? null;

            QuoteItem::create([
                'company_id'    => $this->companyId,
                'quote_id'      => $v2QuoteId,
                'product_id'    => $productId,
                'item_name'     => $v1Item->item_name ?? 'Item',
                'quantity'      => $v1Item->item_quantity ?? 1,
                'price'         => $v1Item->item_price ?? 0,
                'discount'      => $v1Item->item_discount_amount ?? 0,
                'tax_rate_id'   => $taxRateId,
                'subtotal'      => $v1Item->item_subtotal ?? 0,
                'tax_total'     => $v1Item->item_tax_total ?? 0,
                'total'         => $v1Item->item_total ?? 0,
                'description'   => $v1Item->item_description ?? null,
                'display_order' => $v1Item->item_order ?? 0,
            ]);

            $this->stats['quote_items']++;
        }
    }

    /**
     * Import payments from v1
     */
    private function importPayments(): void
    {
        if (! $this->tableExists('ip_payments')) {
            return;
        }

        $payments = DB::connection('mysql')
            ->table(self::TEMP_DB_NAME . '.ip_payments')
            ->get();

        foreach ($payments as $v1Payment) {
            $invoiceId = $this->idMappings['invoices'][$v1Payment->invoice_id] ?? null;
            $customerId = $this->idMappings['clients'][$v1Payment->client_id] ?? null;

            if (! $invoiceId || ! $customerId) {
                continue; // Skip payments without invoices or customers
            }

            Payment::create([
                'company_id'     => $this->companyId,
                'customer_id'    => $customerId,
                'invoice_id'     => $invoiceId,
                'payment_number' => null,
                'payment_method' => $this->mapPaymentMethod($v1Payment->payment_method_id ?? 1),
                'payment_status' => 'paid',
                'paid_at'        => $v1Payment->payment_date ?? now(),
                'payment_amount' => $v1Payment->payment_amount ?? 0,
                'notes'          => $v1Payment->payment_note ?? null,
            ]);

            $this->stats['payments']++;
        }
    }

    /**
     * Map v1 invoice status to v2
     */
    private function mapInvoiceStatus(int $statusId): string
    {
        return match ($statusId) {
            1       => 'draft',
            2       => 'sent',
            3       => 'viewed',
            4       => 'paid',
            5       => 'overdue',
            default => 'draft',
        };
    }

    /**
     * Map v1 quote status to v2
     */
    private function mapQuoteStatus(int $statusId): string
    {
        return match ($statusId) {
            1       => 'draft',
            2       => 'sent',
            3       => 'viewed',
            4       => 'approved',
            5       => 'rejected',
            6       => 'canceled',
            default => 'draft',
        };
    }

    /**
     * Map v1 payment method to v2
     */
    private function mapPaymentMethod(int $methodId): string
    {
        return match ($methodId) {
            1       => 'cash',
            2       => 'bank_transfer',
            3       => 'credit_card',
            4       => 'paypal',
            default => 'other',
        };
    }
}
