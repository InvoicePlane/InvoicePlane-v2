<?php

namespace Modules\Core\Importers;

use Modules\Clients\Importers\CustomerImporter;

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
