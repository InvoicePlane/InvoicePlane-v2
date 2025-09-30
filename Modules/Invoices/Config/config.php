<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Peppol Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Peppol e-invoicing integration.
    | Different providers can be configured here.
    |
    */

    'peppol' => [
        /*
        |--------------------------------------------------------------------------
        | Default Peppol Provider
        |--------------------------------------------------------------------------
        |
        | The default Peppol access point provider to use.
        | Supported: "e_invoice_be", "storecove", "custom"
        |
        */
        'default_provider' => env('PEPPOL_PROVIDER', 'e_invoice_be'),

        /*
        |--------------------------------------------------------------------------
        | E-Invoice.be Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for the e-invoice.be Peppol access point.
        | See: https://api.e-invoice.be/docs
        |
        */
        'e_invoice_be' => [
            'api_key' => env('PEPPOL_E_INVOICE_BE_API_KEY', ''),
            'base_url' => env('PEPPOL_E_INVOICE_BE_BASE_URL', 'https://api.e-invoice.be'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Peppol Document Settings
        |--------------------------------------------------------------------------
        |
        | Default settings for Peppol documents.
        |
        */
        'document' => [
            'currency_code' => env('PEPPOL_CURRENCY_CODE', 'EUR'),
            'default_unit_code' => 'C62', // Unit (piece)
            'default_endpoint_scheme' => 'BE:CBE', // Belgian company number scheme
        ],

        /*
        |--------------------------------------------------------------------------
        | Validation Settings
        |--------------------------------------------------------------------------
        |
        | Settings for validating invoices before sending to Peppol.
        |
        */
        'validation' => [
            'require_customer_peppol_id' => true,
            'require_vat_number' => false,
            'min_invoice_amount' => 0,
        ],
    ],
];
