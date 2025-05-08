<?php

namespace Modules\Core\Importers;

use Modules\Clients\Importers\CustomerImporter;
use Modules\Core\Models\ExpenseImporter;
use Modules\Core\Models\InvoiceImporter;
use Modules\Core\Models\InvoiceItemImporter;
use Modules\Core\Models\PaymentImporter;
use Modules\Core\Models\ProductImporter;
use Modules\Core\Models\QuoteImporter;
use Modules\Core\Models\QuoteItemImporter;

class ImportFactory
{
    public static function create($importType)
    {
        switch ($importType) {
            case 'customers':
                return app()->make(CustomerImporter::class);
            case 'quotes':
                return app()->make(QuoteImporter::class);
            case 'invoices':
                return app()->make(InvoiceImporter::class);
            case 'payments':
                return app()->make(PaymentImporter::class);
            case 'invoiceItems':
                return app()->make(InvoiceItemImporter::class);
            case 'quoteItems':
                return app()->make(QuoteItemImporter::class);
            case 'itemLookups':
                return app()->make(ProductImporter::class);
            case 'expenses':
                return app(ExpenseImporter::class);
        }
    }
}
