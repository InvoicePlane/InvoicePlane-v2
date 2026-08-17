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
        /* Supported: "e_invoice_be", "storecove", "lets_peppol", "super_pdp", "qonto" */

        /*
        |--------------------------------------------------------------------------
        | E-Invoice.be Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for the e-invoice.be Peppol access point.
        | See: https://api.e-invoice.be/docs
        | SDK: https://github.com/e-invoice-be/e-invoice-php
        |
        */
        'e_invoice_be' => [
            'api_key'  => env('PEPPOL_E_INVOICE_BE_API_KEY', ''),
            'base_url' => env('PEPPOL_E_INVOICE_BE_BASE_URL', 'https://api.e-invoice.be'),
            'timeout'  => env('PEPPOL_E_INVOICE_BE_TIMEOUT', 30),
        ],

        /*
        |--------------------------------------------------------------------------
        | Storecove Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for the Storecove Peppol access point.
        | See: https://www.storecove.com/documentation/api
        |
        */
        'storecove' => [
            'api_key'         => env('PEPPOL_STORECOVE_API_KEY', ''),
            'legal_entity_id' => env('PEPPOL_STORECOVE_LEGAL_ENTITY_ID', ''),
            'base_url'        => env('PEPPOL_STORECOVE_BASE_URL', 'https://api.storecove.com/api/v2'),
            'timeout'         => env('PEPPOL_STORECOVE_TIMEOUT', 30),
        ],

        /*
        |--------------------------------------------------------------------------
        | Peppol Document Settings
        |--------------------------------------------------------------------------
        |
        | Default settings for Peppol documents.
        | These can be overridden per company or per invoice.
        |
        */
        'document' => [
            // Currency settings
            'currency_code'     => env('PEPPOL_CURRENCY_CODE', 'EUR'),
            'fallback_currency' => 'EUR',

            // Unit codes (UN/CEFACT)
            'default_unit_code' => env('PEPPOL_UNIT_CODE', 'C62'), // C62 = Unit (piece)

            // Endpoint scheme settings
            'endpoint_scheme_by_country' => [
                'BE' => 'BE:CBE',
                'DE' => 'DE:VAT',
                'FR' => 'FR:SIRENE',
                'IT' => 'IT:VAT',
                'ES' => 'ES:VAT',
                'NL' => 'NL:KVK',
                'NO' => 'NO:ORGNR',
                'DK' => 'DK:CVR',
                'SE' => 'SE:ORGNR',
                'FI' => 'FI:OVT',
                'AT' => 'AT:VAT',
                'CH' => 'CH:UIDB',
                'GB' => 'GB:COH',
            ],
            'default_endpoint_scheme' => env('PEPPOL_ENDPOINT_SCHEME', 'ISO_6523'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Supplier (Company) Configuration
        |--------------------------------------------------------------------------
        |
        | Default supplier details for invoices.
        | These will be pulled from company settings when available.
        |
        */
        'supplier' => [
            'company_name'  => env('PEPPOL_SUPPLIER_NAME', config('app.name')),
            'vat_number'    => env('PEPPOL_SUPPLIER_VAT', ''),
            'street_name'   => env('PEPPOL_SUPPLIER_STREET', ''),
            'city_name'     => env('PEPPOL_SUPPLIER_CITY', ''),
            'postal_zone'   => env('PEPPOL_SUPPLIER_POSTAL', ''),
            'country_code'  => env('PEPPOL_SUPPLIER_COUNTRY', ''),
            'contact_name'  => env('PEPPOL_SUPPLIER_CONTACT', ''),
            'contact_phone' => env('PEPPOL_SUPPLIER_PHONE', ''),
            'contact_email' => env('PEPPOL_SUPPLIER_EMAIL', ''),
        ],

        /*
        |--------------------------------------------------------------------------
        | Format Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for different e-invoice formats.
        |
        */
        'formats' => [
            'default_format' => env('PEPPOL_DEFAULT_FORMAT', 'peppol_bis_3.0'),

            // Country-specific mandatory formats
            'mandatory_formats_by_country' => [
                'IT' => 'fatturapa_1.2',  // Italy requires FatturaPA
                'ES' => 'facturae_3.2',   // Spain requires Facturae for public sector
            ],

            // Format-specific settings
            'ubl' => [
                'version'          => '2.1',
                'customization_id' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            ],

            'cii' => [
                'version' => '16B',
                'profile' => 'EN16931',
            ],
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
            'require_customer_peppol_id' => env('PEPPOL_REQUIRE_PEPPOL_ID', true),
            'require_vat_number'         => env('PEPPOL_REQUIRE_VAT', false),
            'min_invoice_amount'         => env('PEPPOL_MIN_AMOUNT', 0),
            'validate_format_compliance' => env('PEPPOL_VALIDATE_FORMAT', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Feature Flags
        |--------------------------------------------------------------------------
        |
        | Enable or disable specific Peppol features.
        |
        */
        'features' => [
            'enable_tracking'           => env('PEPPOL_ENABLE_TRACKING', true),
            'enable_webhooks'           => env('PEPPOL_ENABLE_WEBHOOKS', false),
            'enable_participant_search' => env('PEPPOL_ENABLE_PARTICIPANT_SEARCH', true),
            'enable_health_checks'      => env('PEPPOL_ENABLE_HEALTH_CHECKS', true),
            'auto_retry_failed'         => env('PEPPOL_AUTO_RETRY', true),
            'max_retries'               => env('PEPPOL_MAX_RETRIES', 5),
        ],

        /*
        |--------------------------------------------------------------------------
        | Country to Scheme Mapping
        |--------------------------------------------------------------------------
        |
        | Mapping of country codes to default Peppol endpoint schemes.
        | Used for auto-suggesting the appropriate scheme when onboarding customers.
        |
        */
        'country_scheme_mapping' => [
            'BE' => 'BE:CBE',
            'DE' => 'DE:VAT',
            'FR' => 'FR:SIRENE',
            'IT' => 'IT:VAT',
            'ES' => 'ES:VAT',
            'NL' => 'NL:KVK',
            'NO' => 'NO:ORGNR',
            'DK' => 'DK:CVR',
            'SE' => 'SE:ORGNR',
            'FI' => 'FI:OVT',
            'AT' => 'AT:VAT',
            'CH' => 'CH:UIDB',
            'GB' => 'GB:COH',
        ],

        /*
        |--------------------------------------------------------------------------
        | Retry Policy
        |--------------------------------------------------------------------------
        |
        | Configuration for automatic retries of failed transmissions.
        | Uses exponential backoff strategy.
        |
        */
        'retry' => [
            'max_attempts'           => env('PEPPOL_MAX_RETRY_ATTEMPTS', 5),
            'backoff_delays'         => [60, 300, 1800, 7200, 21600], // 1min, 5min, 30min, 2h, 6h
            'retry_transient_errors' => true,
            'retry_unknown_errors'   => true,
            'retry_permanent_errors' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Storage Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for storing Peppol artifacts (XML, PDF).
        |
        */
        'storage' => [
            'disk'           => env('PEPPOL_STORAGE_DISK', 'local'),
            'path_template'  => 'peppol/{integration_id}/{year}/{month}/{transmission_id}',
            'retention_days' => env('PEPPOL_RETENTION_DAYS', 2555), // 7 years default
        ],

        /*
        |--------------------------------------------------------------------------
        | Monitoring & Alerting
        |--------------------------------------------------------------------------
        |
        | Thresholds and settings for monitoring Peppol operations.
        |
        */
        'monitoring' => [
            'alert_on_dead_transmission'  => true,
            'dead_transmission_threshold' => 10, // Alert if > 10 dead in 1 hour
            'alert_on_auth_failure'       => true,
            'status_check_interval'       => 15, // minutes
            'reconciliation_interval'     => 60, // minutes
            'old_transmission_threshold'  => 168, // hours (7 days)
        ],
    ],
];
