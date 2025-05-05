<?php

namespace App\IpModules\Import\Importers;

class ImportFactory
{
    public static function create($importType)
    {
        switch ($importType) {
            case 'customers':
                return app()->make(\App\IpModules\Import\Importers\ClientImporter::class);
            case 'quotes':
                return app()->make(\App\IpModules\Import\Importers\QuoteImporter::class);
            case 'invoices':
                return app()->make(\App\IpModules\Import\Importers\InvoiceImporter::class);
            case 'payments':
                return app()->make(\App\IpModules\Import\Importers\PaymentImporter::class);
            case 'invoiceItems':
                return app()->make(\App\IpModules\Import\Importers\InvoiceItemImporter::class);
            case 'quoteItems':
                return app()->make(\App\IpModules\Import\Importers\QuoteItemImporter::class);
            case 'itemLookups':
                return app()->make(\App\IpModules\Import\Importers\ItemLookupImporter::class);
            case 'expenses':
                return app(\App\IpModules\Import\Importers\ExpenseImporter::class);
        }
    }
}
