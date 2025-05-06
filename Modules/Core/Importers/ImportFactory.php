<?php

namespace Modules\Core\Importers;

class ImportFactory
{
    public static function create($importType)
    {
        switch ($importType) {
            case 'customers':
                return app()->make(\Modules\Clients\Importers\CustomerImporter::class);
            case 'quotes':
                return app()->make(\Modules\Core\Importers\QuoteImporter::class);
            case 'invoices':
                return app()->make(\Modules\Core\Importers\InvoiceImporter::class);
            case 'payments':
                return app()->make(\Modules\Core\Importers\PaymentImporter::class);
            case 'invoiceItems':
                return app()->make(\Modules\Core\Importers\InvoiceItemImporter::class);
            case 'quoteItems':
                return app()->make(\Modules\Core\Importers\QuoteItemImporter::class);
            case 'itemLookups':
                return app()->make(\Modules\Core\Importers\ItemLookupImporter::class);
            case 'expenses':
                return app(\Modules\Core\Importers\ExpenseImporter::class);
        }
    }
}
