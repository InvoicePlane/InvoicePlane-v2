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
        ['id' => 'company_name', 'label' => 'ip.report_field_company_name'],
        ['id' => 'company_address_1', 'label' => 'ip.report_field_company_address_1'],
        ['id' => 'company_address_2', 'label' => 'ip.report_field_company_address_2'],
        ['id' => 'company_city', 'label' => 'ip.report_field_company_city'],
        ['id' => 'company_state', 'label' => 'ip.report_field_company_state'],
        ['id' => 'company_zip', 'label' => 'ip.report_field_company_zip'],
        ['id' => 'company_country', 'label' => 'ip.report_field_company_country'],
        ['id' => 'company_phone', 'label' => 'ip.report_field_company_phone'],
        ['id' => 'company_email', 'label' => 'ip.report_field_company_email'],
        ['id' => 'company_vat_id', 'label' => 'ip.report_field_company_vat_id'],
        ['id' => 'company_id_number', 'label' => 'ip.report_field_company_id_number'],
        ['id' => 'company_coc_number', 'label' => 'ip.report_field_company_coc_number'],
    ],

    'customer' => [
        ['id' => 'customer_name', 'label' => 'ip.report_field_customer_name'],
        ['id' => 'customer_address_1', 'label' => 'ip.report_field_customer_address_1'],
        ['id' => 'customer_address_2', 'label' => 'ip.report_field_customer_address_2'],
        ['id' => 'customer_city', 'label' => 'ip.report_field_customer_city'],
        ['id' => 'customer_state', 'label' => 'ip.report_field_customer_state'],
        ['id' => 'customer_zip', 'label' => 'ip.report_field_customer_zip'],
        ['id' => 'customer_country', 'label' => 'ip.report_field_customer_country'],
        ['id' => 'customer_phone', 'label' => 'ip.report_field_customer_phone'],
        ['id' => 'customer_email', 'label' => 'ip.report_field_customer_email'],
        ['id' => 'customer_vat_id', 'label' => 'ip.report_field_customer_vat_id'],
    ],

    'invoice' => [
        ['id' => 'invoice_number', 'label' => 'ip.report_field_invoice_number'],
        ['id' => 'invoice_date', 'label' => 'ip.report_field_invoice_date', 'format' => 'date'],
        ['id' => 'invoice_date_created', 'label' => 'ip.report_field_invoice_date_created', 'format' => 'date'],
        ['id' => 'invoice_date_due', 'label' => 'ip.report_field_invoice_date_due', 'format' => 'date'],
        ['id' => 'invoice_guest_url', 'label' => 'ip.report_field_invoice_guest_url', 'format' => 'url'],
        ['id' => 'invoice_item_subtotal', 'label' => 'ip.report_field_invoice_item_subtotal', 'format' => 'currency'],
        ['id' => 'invoice_item_tax_total', 'label' => 'ip.report_field_invoice_item_tax_total', 'format' => 'currency'],
        ['id' => 'invoice_total', 'label' => 'ip.report_field_invoice_total', 'format' => 'currency'],
        ['id' => 'invoice_paid', 'label' => 'ip.report_field_invoice_paid', 'format' => 'currency'],
        ['id' => 'invoice_balance', 'label' => 'ip.report_field_invoice_balance', 'format' => 'currency'],
        ['id' => 'invoice_status', 'label' => 'ip.report_field_invoice_status'],
        ['id' => 'invoice_notes', 'label' => 'ip.report_field_invoice_notes'],
        ['id' => 'invoice_terms', 'label' => 'ip.report_field_invoice_terms'],
    ],

    'invoice_item' => [
        ['id' => 'item_description', 'label' => 'ip.report_field_item_description'],
        ['id' => 'item_name', 'label' => 'ip.report_field_item_name'],
        ['id' => 'item_quantity', 'label' => 'ip.report_field_item_quantity', 'format' => 'number'],
        ['id' => 'item_price', 'label' => 'ip.report_field_item_price', 'format' => 'currency'],
        ['id' => 'item_subtotal', 'label' => 'ip.report_field_item_subtotal', 'format' => 'currency'],
        ['id' => 'item_tax_name', 'label' => 'ip.report_field_item_tax_name'],
        ['id' => 'item_tax_rate', 'label' => 'ip.report_field_item_tax_rate', 'format' => 'percentage'],
        ['id' => 'item_tax_amount', 'label' => 'ip.report_field_item_tax_amount', 'format' => 'currency'],
        ['id' => 'item_total', 'label' => 'ip.report_field_item_total', 'format' => 'currency'],
        ['id' => 'item_discount', 'label' => 'ip.report_field_item_discount', 'format' => 'currency'],
    ],

    'quote' => [
        ['id' => 'quote_number', 'label' => 'ip.report_field_quote_number'],
        ['id' => 'quote_date', 'label' => 'ip.report_field_quote_date', 'format' => 'date'],
        ['id' => 'quote_date_created', 'label' => 'ip.report_field_quote_date_created', 'format' => 'date'],
        ['id' => 'quote_date_expires', 'label' => 'ip.report_field_quote_date_expires', 'format' => 'date'],
        ['id' => 'quote_guest_url', 'label' => 'ip.report_field_quote_guest_url', 'format' => 'url'],
        ['id' => 'quote_item_subtotal', 'label' => 'ip.report_field_quote_item_subtotal', 'format' => 'currency'],
        ['id' => 'quote_tax_total', 'label' => 'ip.report_field_quote_tax_total', 'format' => 'currency'],
        ['id' => 'quote_item_discount', 'label' => 'ip.report_field_quote_item_discount', 'format' => 'currency'],
        ['id' => 'quote_total', 'label' => 'ip.report_field_quote_total', 'format' => 'currency'],
        ['id' => 'quote_status', 'label' => 'ip.report_field_quote_status'],
        ['id' => 'quote_notes', 'label' => 'ip.report_field_quote_notes'],
    ],

    'quote_item' => [
        ['id' => 'quote_item_description', 'label' => 'ip.report_field_quote_item_description'],
        ['id' => 'quote_item_name', 'label' => 'ip.report_field_quote_item_name'],
        ['id' => 'quote_item_quantity', 'label' => 'ip.report_field_quote_item_quantity', 'format' => 'number'],
        ['id' => 'quote_item_price', 'label' => 'ip.report_field_quote_item_price', 'format' => 'currency'],
        ['id' => 'quote_item_subtotal', 'label' => 'ip.report_field_quote_item_subtotal', 'format' => 'currency'],
        ['id' => 'quote_item_tax_name', 'label' => 'ip.report_field_quote_item_tax_name'],
        ['id' => 'quote_item_tax_rate', 'label' => 'ip.report_field_quote_item_tax_rate', 'format' => 'percentage'],
        ['id' => 'quote_item_total', 'label' => 'ip.report_field_quote_item_total', 'format' => 'currency'],
        ['id' => 'quote_item_discount', 'label' => 'ip.report_field_quote_item_discount', 'format' => 'currency'],
    ],

    'payment' => [
        ['id' => 'payment_date', 'label' => 'ip.report_field_payment_date', 'format' => 'date'],
        ['id' => 'payment_amount', 'label' => 'ip.report_field_payment_amount', 'format' => 'currency'],
        ['id' => 'payment_method', 'label' => 'ip.report_field_payment_method'],
        ['id' => 'payment_note', 'label' => 'ip.report_field_payment_note'],
        ['id' => 'payment_reference', 'label' => 'ip.report_field_payment_reference'],
    ],

    'project' => [
        ['id' => 'project_name', 'label' => 'ip.report_field_project_name'],
        ['id' => 'project_description', 'label' => 'ip.report_field_project_description'],
        ['id' => 'project_start_date', 'label' => 'ip.report_field_project_start_date', 'format' => 'date'],
        ['id' => 'project_end_date', 'label' => 'ip.report_field_project_end_date', 'format' => 'date'],
        ['id' => 'project_status', 'label' => 'ip.report_field_project_status'],
    ],

    'task' => [
        ['id' => 'task_name', 'label' => 'ip.report_field_task_name'],
        ['id' => 'task_description', 'label' => 'ip.report_field_task_description'],
        ['id' => 'task_start_date', 'label' => 'ip.report_field_task_start_date', 'format' => 'date'],
        ['id' => 'task_finish_date', 'label' => 'ip.report_field_task_finish_date', 'format' => 'date'],
        ['id' => 'task_hours', 'label' => 'ip.report_field_task_hours', 'format' => 'number'],
        ['id' => 'task_rate', 'label' => 'ip.report_field_task_rate', 'format' => 'currency'],
    ],

    'expense' => [
        ['id' => 'expense_date', 'label' => 'ip.report_field_expense_date', 'format' => 'date'],
        ['id' => 'expense_category', 'label' => 'ip.report_field_expense_category'],
        ['id' => 'expense_amount', 'label' => 'ip.report_field_expense_amount', 'format' => 'currency'],
        ['id' => 'expense_description', 'label' => 'ip.report_field_expense_description'],
        ['id' => 'expense_vendor', 'label' => 'ip.report_field_expense_vendor'],
    ],

    'relation' => [
        ['id' => 'relation_name', 'label' => 'ip.report_field_relation_name'],
        ['id' => 'relation_address_1', 'label' => 'ip.report_field_relation_address_1'],
        ['id' => 'relation_address_2', 'label' => 'ip.report_field_relation_address_2'],
        ['id' => 'relation_city', 'label' => 'ip.report_field_relation_city'],
        ['id' => 'relation_state', 'label' => 'ip.report_field_relation_state'],
        ['id' => 'relation_zip', 'label' => 'ip.report_field_relation_zip'],
        ['id' => 'relation_country', 'label' => 'ip.report_field_relation_country'],
        ['id' => 'relation_phone', 'label' => 'ip.report_field_relation_phone'],
        ['id' => 'relation_email', 'label' => 'ip.report_field_relation_email'],
    ],

    'sumex' => [
        ['id' => 'sumex_casedate', 'label' => 'ip.report_field_sumex_casedate', 'format' => 'date'],
        ['id' => 'sumex_casenumber', 'label' => 'ip.report_field_sumex_casenumber'],
    ],

    'common' => [
        ['id' => 'current_date', 'label' => 'ip.report_field_current_date', 'format' => 'date'],
        ['id' => 'footer_notes', 'label' => 'ip.report_field_footer_notes'],
        ['id' => 'page_number', 'label' => 'ip.report_field_page_number'],
        ['id' => 'total_pages', 'label' => 'ip.report_field_total_pages'],
    ],
];
