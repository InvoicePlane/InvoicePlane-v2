<?php

return [
    'address_type' => [
        'billing'  => 'Rechnungsadresse',
        'shipping' => 'Lieferadresse',
        'office'   => 'Büro',
    ],
    'relation_type' => [
        'partner' => 'Partner',
        'lead'    => 'Interessent',
    ],
    'communication_type' => [
        'email'      => 'E-Mail',
        'phone'      => 'Telefon',
        'fax'        => 'Fax',
        'mobile'     => 'Mobiltelefon',
        'whatsapp'   => 'WhatsApp',
        'invoice_cc' => 'Rechnungs-E-Mail (CC)',
    ],
    'payment_status' => [
        'completed'          => 'Abgeschlossen',
        'failed'             => 'Fehlgeschlagen',
        'pending'            => 'Ausstehend',
        'refunded'           => 'Erstattet',
        'refunded_partially' => 'Teilweise erstattet',
    ],
    'mail_type' => [
        'reminder' => 'Zahlungserinnerung',
        'sent'     => 'Versendet',
    ],
    'custom_field_type' => [
        'text'    => 'Einzeiliger Text',
        'number'  => 'Mehrzeiliger Text',
        'date'    => 'Datumsauswahl',
        'boolean' => 'Ja/Nein',
    ],
    'report_band' => [
        'header'       => 'Kopfzeile',
        'group_header' => 'Gruppenkopf',
        'details'      => 'Details',
        'group_footer' => 'Gruppenfuß',
        'footer'       => 'Fußzeile',
    ],
    'tax_rate_type' => [
        'exclusive' => 'Zuzüglich Steuer',
        'inclusive' => 'Inklusive Steuer',
        'zero'      => 'Nullsteuersatz',
        'exempt'    => 'Steuerbefreit',
    ],
    'user_type' => [
        'admin'  => 'Administrator',
        'client' => 'Kunde',
    ],
    'user_role' => [
        'super_admin'    => 'Superadministrator',
        'admin'          => 'Administrator',
        'assist'         => 'Assistenz',
        'customer_admin' => 'Kundenadministrator',
        'customer'       => 'Kunde',
    ],
    'email_template_type' => [
        'text'    => 'Einzeiliger Text',
        'number'  => 'Mehrzeiliger Text',
        'date'    => 'Datumsauswahl',
        'boolean' => 'Ja/Nein',
    ],
    'recurring_frequency' => [
        'daily'     => 'Täglich',
        'weekly'    => 'Wöchentlich',
        'monthly'   => 'Monatlich',
        'yearly'    => 'Jährlich',
        'quarterly' => 'Vierteljährlich',
    ],
    'expense_status' => [
        'draft'      => 'Entwurf',
        'submitted'  => 'Eingereicht',
        'approved'   => 'Genehmigt',
        'reimbursed' => 'Erstattet',
        'billed'     => 'Abgerechnet',
        'paid'       => 'Bezahlt',
    ],
    'expense_type' => [
        'one_time'    => 'Einmalig',
        'recurring'   => 'Wiederkehrend',
        'travel'      => 'Reise',
        'utility'     => 'Versorgung',
        'maintenance' => 'Wartung',
        'fixed'       => 'Fixkosten',
    ],
    'project_status' => [
        'on_hold'   => 'Pausiert',
        'completed' => 'Abgeschlossen',
        'planned'   => 'Geplant',
        'active'    => 'Aktiv',
        'cancelled' => 'Storniert',
    ],
];
