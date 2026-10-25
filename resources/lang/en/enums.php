<?php

return [
    'address_type' => [
        'billing'  => 'Billing',
        'shipping' => 'Shipping',
        'office'   => 'Office',
    ],
    'relation_type' => [
        'partner' => 'Partner',
        'lead'    => 'Lead',
    ],
    'communication_type' => [
        'email'      => 'Email',
        'phone'      => 'Phone',
        'fax'        => 'Fax',
        'mobile'     => 'Mobile',
        'whatsapp'   => 'Whatsapp',
        'invoice_cc' => 'Invoice CC Email',
    ],
    'payment_status' => [
        'completed'          => 'Completed',
        'failed'             => 'Failed',
        'pending'            => 'Pending',
        'refunded'           => 'Refunded',
        'refunded_partially' => 'Partially Refunded',
    ],
    'mail_type' => [
        'reminder' => 'Reminder',
        'sent'     => 'Sent',
    ],
    'custom_field_type' => [
        'text'    => 'Single Line Text',
        'number'  => 'Multi Line Text',
        'date'    => 'Date Picker',
        'boolean' => 'Boolean',
    ],
    'report_band' => [
        'header'       => 'Header',
        'group_header' => 'Group Header',
        'details'      => 'Details',
        'group_footer' => 'Group Footer',
        'footer'       => 'Footer',
    ],
    'tax_rate_type' => [
        'exclusive' => 'Exclusive',
        'inclusive' => 'Inclusive',
        'zero'      => 'Zero',
        'exempt'    => 'Exempt',
    ],
    'user_type' => [
        'admin'  => 'Administrator',
        'client' => 'Client',
    ],
    'user_role' => [
        'super_admin'    => 'Super Admin',
        'admin'          => 'admin',
        'assist'         => 'assist',
        'customer_admin' => 'Client Admin',
        'customer'       => 'client',
    ],
    'email_template_type' => [
        'text'    => 'Single Line Text',
        'number'  => 'Multi Line Text',
        'date'    => 'Date Picker',
        'boolean' => 'Boolean',
    ],
    'recurring_frequency' => [
        'daily'     => 'Daily',
        'weekly'    => 'Weekly',
        'monthly'   => 'Monthly',
        'yearly'    => 'Yearly',
        'quarterly' => 'Quarterly',
    ],
    'expense_status' => [
        'draft'      => 'Draft',
        'submitted'  => 'Submitted',
        'approved'   => 'Approved',
        'reimbursed' => 'Reimbursed',
        'billed'     => 'Billed',
        'paid'       => 'Paid',
    ],
    'expense_type' => [
        'one_time'    => 'One Time',
        'recurring'   => 'Recurring',
        'travel'      => 'Travel',
        'utility'     => 'Utility',
        'maintenance' => 'Maintenance',
        'fixed'       => 'Fixed',
    ],
    'project_status' => [
        'on_hold'   => 'On Hold',
        'completed' => 'Completed',
        'planned'   => 'Planned',
        'active'    => 'Active',
        'cancelled' => 'Cancelled',
    ],
];
