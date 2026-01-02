<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Block Available Fields
    |--------------------------------------------------------------------------
    |
    | This configuration defines all available fields that can be used in
    | report blocks. Fields are grouped by data source for better organization.
    | Each field includes an ID, label, and optional formatting/transformation.
    |
    */

    'company' => [
        ['id' => 'company_name', 'label' => 'Company Name'],
        ['id' => 'company_address_1', 'label' => 'Company Address Line 1'],
        ['id' => 'company_address_2', 'label' => 'Company Address Line 2'],
        ['id' => 'company_city', 'label' => 'Company City'],
        ['id' => 'company_state', 'label' => 'Company State/Province'],
        ['id' => 'company_zip', 'label' => 'Company ZIP/Postal Code'],
        ['id' => 'company_country', 'label' => 'Company Country'],
        ['id' => 'company_phone', 'label' => 'Company Phone'],
        ['id' => 'company_email', 'label' => 'Company Email'],
        ['id' => 'company_vat_id', 'label' => 'Company VAT ID'],
        ['id' => 'company_id_number', 'label' => 'Company ID Number'],
        ['id' => 'company_coc_number', 'label' => 'Company CoC Number'],
    ],

    'customer' => [
        ['id' => 'customer_name', 'label' => 'Customer Name'],
        ['id' => 'customer_address_1', 'label' => 'Customer Address Line 1'],
        ['id' => 'customer_address_2', 'label' => 'Customer Address Line 2'],
        ['id' => 'customer_city', 'label' => 'Customer City'],
        ['id' => 'customer_state', 'label' => 'Customer State/Province'],
        ['id' => 'customer_zip', 'label' => 'Customer ZIP/Postal Code'],
        ['id' => 'customer_country', 'label' => 'Customer Country'],
        ['id' => 'customer_phone', 'label' => 'Customer Phone'],
        ['id' => 'customer_email', 'label' => 'Customer Email'],
        ['id' => 'customer_vat_id', 'label' => 'Customer VAT ID'],
    ],

    'invoice' => [
        ['id' => 'invoice_number', 'label' => 'Invoice Number'],
        ['id' => 'invoice_date', 'label' => 'Invoice Date', 'format' => 'date'],
        ['id' => 'invoice_date_created', 'label' => 'Invoice Date Created', 'format' => 'date'],
        ['id' => 'invoice_date_due', 'label' => 'Invoice Due Date', 'format' => 'date'],
        ['id' => 'invoice_guest_url', 'label' => 'Invoice Guest URL', 'format' => 'url'],
        ['id' => 'invoice_item_subtotal', 'label' => 'Invoice Subtotal', 'format' => 'currency'],
        ['id' => 'invoice_item_tax_total', 'label' => 'Invoice Tax Total', 'format' => 'currency'],
        ['id' => 'invoice_total', 'label' => 'Invoice Total', 'format' => 'currency'],
        ['id' => 'invoice_paid', 'label' => 'Invoice Amount Paid', 'format' => 'currency'],
        ['id' => 'invoice_balance', 'label' => 'Invoice Balance', 'format' => 'currency'],
        ['id' => 'invoice_status', 'label' => 'Invoice Status'],
        ['id' => 'invoice_notes', 'label' => 'Invoice Notes'],
        ['id' => 'invoice_terms', 'label' => 'Invoice Terms'],
    ],

    'invoice_item' => [
        ['id' => 'item_description', 'label' => 'Item Description'],
        ['id' => 'item_name', 'label' => 'Item Name'],
        ['id' => 'item_quantity', 'label' => 'Item Quantity', 'format' => 'number'],
        ['id' => 'item_price', 'label' => 'Item Price', 'format' => 'currency'],
        ['id' => 'item_subtotal', 'label' => 'Item Subtotal', 'format' => 'currency'],
        ['id' => 'item_tax_name', 'label' => 'Item Tax Name'],
        ['id' => 'item_tax_rate', 'label' => 'Item Tax Rate', 'format' => 'percentage'],
        ['id' => 'item_tax_amount', 'label' => 'Item Tax Amount', 'format' => 'currency'],
        ['id' => 'item_total', 'label' => 'Item Total', 'format' => 'currency'],
        ['id' => 'item_discount', 'label' => 'Item Discount', 'format' => 'currency'],
    ],

    'quote' => [
        ['id' => 'quote_number', 'label' => 'Quote Number'],
        ['id' => 'quote_date', 'label' => 'Quote Date', 'format' => 'date'],
        ['id' => 'quote_date_created', 'label' => 'Quote Date Created', 'format' => 'date'],
        ['id' => 'quote_date_expires', 'label' => 'Quote Expiry Date', 'format' => 'date'],
        ['id' => 'quote_guest_url', 'label' => 'Quote Guest URL', 'format' => 'url'],
        ['id' => 'quote_item_subtotal', 'label' => 'Quote Subtotal', 'format' => 'currency'],
        ['id' => 'quote_tax_total', 'label' => 'Quote Tax Total', 'format' => 'currency'],
        ['id' => 'quote_item_discount', 'label' => 'Quote Discount', 'format' => 'currency'],
        ['id' => 'quote_total', 'label' => 'Quote Total', 'format' => 'currency'],
        ['id' => 'quote_status', 'label' => 'Quote Status'],
        ['id' => 'quote_notes', 'label' => 'Quote Notes'],
    ],

    'quote_item' => [
        ['id' => 'quote_item_description', 'label' => 'Quote Item Description'],
        ['id' => 'quote_item_name', 'label' => 'Quote Item Name'],
        ['id' => 'quote_item_quantity', 'label' => 'Quote Item Quantity', 'format' => 'number'],
        ['id' => 'quote_item_price', 'label' => 'Quote Item Price', 'format' => 'currency'],
        ['id' => 'quote_item_subtotal', 'label' => 'Quote Item Subtotal', 'format' => 'currency'],
        ['id' => 'quote_item_tax_name', 'label' => 'Quote Item Tax Name'],
        ['id' => 'quote_item_tax_rate', 'label' => 'Quote Item Tax Rate', 'format' => 'percentage'],
        ['id' => 'quote_item_total', 'label' => 'Quote Item Total', 'format' => 'currency'],
        ['id' => 'quote_item_discount', 'label' => 'Quote Item Discount', 'format' => 'currency'],
    ],

    'payment' => [
        ['id' => 'payment_date', 'label' => 'Payment Date', 'format' => 'date'],
        ['id' => 'payment_amount', 'label' => 'Payment Amount', 'format' => 'currency'],
        ['id' => 'payment_method', 'label' => 'Payment Method'],
        ['id' => 'payment_note', 'label' => 'Payment Note'],
        ['id' => 'payment_reference', 'label' => 'Payment Reference'],
    ],

    'project' => [
        ['id' => 'project_name', 'label' => 'Project Name'],
        ['id' => 'project_description', 'label' => 'Project Description'],
        ['id' => 'project_start_date', 'label' => 'Project Start Date', 'format' => 'date'],
        ['id' => 'project_end_date', 'label' => 'Project End Date', 'format' => 'date'],
        ['id' => 'project_status', 'label' => 'Project Status'],
    ],

    'task' => [
        ['id' => 'task_name', 'label' => 'Task Name'],
        ['id' => 'task_description', 'label' => 'Task Description'],
        ['id' => 'task_start_date', 'label' => 'Task Start Date', 'format' => 'date'],
        ['id' => 'task_finish_date', 'label' => 'Task Finish Date', 'format' => 'date'],
        ['id' => 'task_hours', 'label' => 'Task Hours', 'format' => 'number'],
        ['id' => 'task_rate', 'label' => 'Task Rate', 'format' => 'currency'],
    ],

    'expense' => [
        ['id' => 'expense_date', 'label' => 'Expense Date', 'format' => 'date'],
        ['id' => 'expense_category', 'label' => 'Expense Category'],
        ['id' => 'expense_amount', 'label' => 'Expense Amount', 'format' => 'currency'],
        ['id' => 'expense_description', 'label' => 'Expense Description'],
        ['id' => 'expense_vendor', 'label' => 'Expense Vendor'],
    ],

    'relation' => [
        ['id' => 'relation_name', 'label' => 'Relation Name'],
        ['id' => 'relation_address_1', 'label' => 'Relation Address Line 1'],
        ['id' => 'relation_address_2', 'label' => 'Relation Address Line 2'],
        ['id' => 'relation_city', 'label' => 'Relation City'],
        ['id' => 'relation_state', 'label' => 'Relation State/Province'],
        ['id' => 'relation_zip', 'label' => 'Relation ZIP/Postal Code'],
        ['id' => 'relation_country', 'label' => 'Relation Country'],
        ['id' => 'relation_phone', 'label' => 'Relation Phone'],
        ['id' => 'relation_email', 'label' => 'Relation Email'],
    ],

    'sumex' => [
        ['id' => 'sumex_casedate', 'label' => 'Sumex Case Date', 'format' => 'date'],
        ['id' => 'sumex_casenumber', 'label' => 'Sumex Case Number'],
    ],

    'common' => [
        ['id' => 'current_date', 'label' => 'Current Date', 'format' => 'date'],
        ['id' => 'footer_notes', 'label' => 'Footer Notes'],
        ['id' => 'page_number', 'label' => 'Page Number'],
        ['id' => 'total_pages', 'label' => 'Total Pages'],
    ],
];
