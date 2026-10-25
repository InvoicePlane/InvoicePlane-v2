<?php

return [
    'clients' => [
        'itemTitle'       => 'Kunde',
        'collectionTitle' => 'Kunden',
        'inputs'          => [
            'client_date_created' => [
                'label'       => 'Erstellungsdatum des Kunden',
                'placeholder' => 'Erstellungsdatum des Kunden',
            ],
            'client_date_modified' => [
                'label'       => 'Änderungsdatum des Kunden',
                'placeholder' => 'Änderungsdatum des Kunden',
            ],
            'client_name' => [
                'label'       => 'Name des Kunden',
                'placeholder' => 'Name des Kunden',
            ],
            'client_address_1' => [
                'label'       => 'Kundenadresse 1',
                'placeholder' => 'Kundenadresse 1',
            ],
            'client_address_2' => [
                'label'       => 'Kundenadresse 2',
                'placeholder' => 'Kundenadresse 2',
            ],
            'client_city' => [
                'label'       => 'Ort des Kunden',
                'placeholder' => 'Ort des Kunden',
            ],
            'client_state' => [
                'label'       => 'Bundesland des Kunden',
                'placeholder' => 'Bundesland des Kunden',
            ],
            'client_zip' => [
                'label'       => 'Postleitzahl des Kunden',
                'placeholder' => 'Postleitzahl des Kunden',
            ],
            'client_country' => [
                'label'       => 'Land des Kunden',
                'placeholder' => 'Land des Kunden',
            ],
            'client_phone' => [
                'label'       => 'Telefonnummer des Kunden',
                'placeholder' => 'Telefonnummer des Kunden',
            ],
            'client_fax' => [
                'label'       => 'Faxnummer des Kunden',
                'placeholder' => 'Faxnummer des Kunden',
            ],
            'client_mobile' => [
                'label'       => 'Mobiltelefonnummer des Kunden',
                'placeholder' => 'Mobiltelefonnummer des Kunden',
            ],
            'client_email' => [
                'label'       => 'E-Mail-Adresse des Kunden',
                'placeholder' => 'E-Mail-Adresse des Kunden',
            ],
            'client_web' => [
                'label'       => 'Webseite des Kunden',
                'placeholder' => 'Webseite des Kunden',
            ],
            'client_vat_id' => [
                'label'       => 'Umsatzsteuer-ID des Kunden',
                'placeholder' => 'Umsatzsteuer-ID des Kunden',
            ],
            'client_tax_code' => [
                'label'       => 'Steuernummer des Kunden',
                'placeholder' => 'Steuernummer des Kunden',
            ],
            'client_language' => [
                'label'       => 'Sprache des Kunden',
                'placeholder' => 'Sprache des Kunden',
            ],
            'client_active' => [
                'label'       => 'Kunde aktiv',
                'placeholder' => 'Kunde aktiv',
            ],
            'client_surname' => [
                'label'       => 'Nachname des Kunden',
                'placeholder' => 'Nachname des Kunden',
            ],
            'client_avs' => [
                'label'       => 'AHV-Nummer des Kunden',
                'placeholder' => 'AHV-Nummer des Kunden',
            ],
            'client_insurednumber' => [
                'label'       => 'Versicherten-Nummer des Kunden',
                'placeholder' => 'Versicherten-Nummer des Kunden',
            ],
            'client_veka' => [
                'label'       => 'VEKA des Kunden',
                'placeholder' => 'VEKA des Kunden',
            ],
            'client_birthdate' => [
                'label'       => 'Geburtsdatum des Kunden',
                'placeholder' => 'Geburtsdatum des Kunden',
            ],
            'client_gender' => [
                'label'       => 'Geschlecht des Kunden',
                'placeholder' => 'Geschlecht des Kunden',
            ],
        ],
    ],
    'imports' => [
        'itemTitle'       => 'Import',
        'collectionTitle' => 'Importe',
        'inputs'          => [],
    ],
    'quotes' => [
        'itemTitle'       => 'Angebot',
        'collectionTitle' => 'Angebote',
        'inputs'          => [
            'invoice_id' => [
                'label'       => 'Rechnungs-ID',
                'placeholder' => 'Rechnungs-ID',
            ],
            'user_id' => [
                'label'       => 'Benutzer-ID',
                'placeholder' => 'Benutzer-ID',
            ],
            'document_group_id' => [
                'label'       => 'Rechnungskreis-ID',
                'placeholder' => 'Rechnungskreis-ID',
            ],
            'quote_status_id' => [
                'label'       => 'Angebotsstatus-ID',
                'placeholder' => 'Angebotsstatus-ID',
            ],
            'quote_date_created' => [
                'label'       => 'Erstellungsdatum des Angebots',
                'placeholder' => 'Erstellungsdatum des Angebots',
            ],
            'quote_date_modified' => [
                'label'       => 'Änderungsdatum des Angebots',
                'placeholder' => 'Änderungsdatum des Angebots',
            ],
            'quote_date_expires' => [
                'label'       => 'Ablaufdatum des Angebots',
                'placeholder' => 'Ablaufdatum des Angebots',
            ],
            'quote_number' => [
                'label'       => 'Angebotsnummer',
                'placeholder' => 'Angebotsnummer',
            ],
            'quote_discount_amount' => [
                'label'       => 'Rabattbetrag des Angebots',
                'placeholder' => 'Rabattbetrag des Angebots',
            ],
            'quote_discount_percent' => [
                'label'       => 'Angebotsrabatt in Prozent',
                'placeholder' => 'Angebotsrabatt in Prozent',
            ],
            'url_key' => [
                'label'       => 'URL-Schlüssel des Angebots',
                'placeholder' => 'URL-Schlüssel des Angebots',
            ],
            'quote_password' => [
                'label'       => 'Passwort des Angebots',
                'placeholder' => 'Passwort des Angebots',
            ],
            'notes' => [
                'label'       => 'Notizen',
                'placeholder' => 'Notizen',
            ],
        ],
    ],
    'invoices' => [
        'itemTitle'       => 'Rechnung',
        'collectionTitle' => 'Rechnungen',
        'inputs'          => [
            'user_id' => [
                'label'       => 'Benutzer-ID',
                'placeholder' => 'Benutzer-ID',
            ],
            'customer_id' => [
                'label'       => 'Kunden-ID',
                'placeholder' => 'Kunden-ID',
            ],
            'document_group_id' => [
                'label'       => 'Rechnungskreis-ID',
                'placeholder' => 'Rechnungskreis-ID',
            ],
            'invoice_status' => [
                'label'       => 'Rechnungsstatus-ID',
                'placeholder' => 'Rechnungsstatus-ID',
            ],
            'is_read_only' => [
                'label'       => 'Schreibgeschützt',
                'placeholder' => 'Schreibgeschützt',
            ],
            'invoice_password' => [
                'label'       => 'Passwort der Rechnung',
                'placeholder' => 'Passwort der Rechnung',
            ],
            'invoice_date_created' => [
                'label'       => 'Erstellungsdatum der Rechnung',
                'placeholder' => 'Erstellungsdatum der Rechnung',
            ],
            'invoice_time_created' => [
                'label'       => 'Erstellungszeit der Rechnung',
                'placeholder' => 'Erstellungszeit der Rechnung',
            ],
            'invoice_date_modified' => [
                'label'       => 'Änderungsdatum der Rechnung',
                'placeholder' => 'Änderungsdatum der Rechnung',
            ],
            'invoice_due_at' => [
                'label'       => 'Fälligkeitsdatum der Rechnung',
                'placeholder' => 'Fälligkeitsdatum der Rechnung',
            ],
            'invoice_number' => [
                'label'       => 'Rechnungsnummer',
                'placeholder' => 'Rechnungsnummer',
            ],
            'invoice_discount_amount' => [
                'label'       => 'Rabattbetrag der Rechnung',
                'placeholder' => 'Rabattbetrag der Rechnung',
            ],
            'invoice_discount_percent' => [
                'label'       => 'Rechnungsrabatt in Prozent',
                'placeholder' => 'Rechnungsrabatt in Prozent',
            ],
            'invoice_terms' => [
                'label'       => 'Rechnungskonditionen',
                'placeholder' => 'Rechnungskonditionen',
            ],
            'invoice_url_key' => [
                'label'       => 'URL-Schlüssel der Rechnung',
                'placeholder' => 'URL-Schlüssel der Rechnung',
            ],
            'payment_method' => [
                'label'       => 'Zahlungsart',
                'placeholder' => 'Zahlungsart',
            ],
            'creditinvoice_parent_id' => [
                'label'       => 'ID der Ursprungsrechnung der Gutschrift',
                'placeholder' => 'ID der Ursprungsrechnung der Gutschrift',
            ],
        ],
    ],
    'invoice_recurring' => [
        'itemTitle'       => 'Wiederkehrende Rechnung',
        'collectionTitle' => 'Wiederkehrende Rechnungen',
    ],
    'payments' => [
        'itemTitle'       => 'Zahlung',
        'collectionTitle' => 'Zahlungen',
    ],
    'products' => [
        'itemTitle'       => 'Produkt',
        'collectionTitle' => 'Produkte',
        'inputs'          => [
            'family_id' => [
                'label'       => 'Produktfamilien-ID',
                'placeholder' => 'Produktfamilien-ID',
            ],
            'product_sku' => [
                'label'       => 'Produkt-SKU',
                'placeholder' => 'Produkt-SKU',
            ],
            'item_name' => [
                'label'       => 'Produktname',
                'placeholder' => 'Produktname',
            ],
            'product_description' => [
                'label'       => 'Produktbeschreibung',
                'placeholder' => 'Produktbeschreibung',
            ],
            'product_price' => [
                'label'       => 'Produktpreis',
                'placeholder' => 'Produktpreis',
            ],
            'purchase_price' => [
                'label'       => 'Einkaufspreis',
                'placeholder' => 'Einkaufspreis',
            ],
            'provider_name' => [
                'label'       => 'Name des Lieferanten',
                'placeholder' => 'Name des Lieferanten',
            ],
            'tax_rate_id' => [
                'label'       => 'Steuersatz-ID',
                'placeholder' => 'Steuersatz-ID',
            ],
            'unit_id' => [
                'label'       => 'Einheiten-ID',
                'placeholder' => 'Einheiten-ID',
            ],
            'product_tariff' => [
                'label'       => 'Produkttarif',
                'placeholder' => 'Produkttarif',
            ],
        ],
    ],
    'users' => [
        'itemTitle'       => 'Benutzer',
        'collectionTitle' => 'Benutzer',
        'inputs'          => [
            'user_type' => [
                'label'       => 'Benutzer-Typ',
                'placeholder' => 'Benutzer-Typ',
            ],
            'user_active' => [
                'label'       => 'Benutzer aktiv',
                'placeholder' => 'Benutzer aktiv',
            ],
            'user_date_created' => [
                'label'       => 'Erstellungsdatum des Benutzers',
                'placeholder' => 'Erstellungsdatum des Benutzers',
            ],
            'user_date_modified' => [
                'label'       => 'Änderungsdatum des Benutzers',
                'placeholder' => 'Änderungsdatum des Benutzers',
            ],
            'user_language' => [
                'label'       => 'Sprache des Benutzers',
                'placeholder' => 'Sprache des Benutzers',
            ],
            'user_name' => [
                'label'       => 'Name des Benutzers',
                'placeholder' => 'Name des Benutzers',
            ],
            'user_company' => [
                'label'       => 'Unternehmen des Benutzers',
                'placeholder' => 'Unternehmen des Benutzers',
            ],
            'user_address_1' => [
                'label'       => 'Benutzeradresse 1',
                'placeholder' => 'Benutzeradresse 1',
            ],
            'user_address_2' => [
                'label'       => 'Benutzeradresse 2',
                'placeholder' => 'Benutzeradresse 2',
            ],
            'user_city' => [
                'label'       => 'Ort des Benutzers',
                'placeholder' => 'Ort des Benutzers',
            ],
            'user_state' => [
                'label'       => 'Bundesland des Benutzers',
                'placeholder' => 'Bundesland des Benutzers',
            ],
            'user_zip' => [
                'label'       => 'Postleitzahl des Benutzers',
                'placeholder' => 'Postleitzahl des Benutzers',
            ],
            'user_country' => [
                'label'       => 'Land des Benutzers',
                'placeholder' => 'Land des Benutzers',
            ],
            'user_phone' => [
                'label'       => 'Telefonnummer des Benutzers',
                'placeholder' => 'Telefonnummer des Benutzers',
            ],
            'user_fax' => [
                'label'       => 'Faxnummer des Benutzers',
                'placeholder' => 'Faxnummer des Benutzers',
            ],
            'user_mobile' => [
                'label'       => 'Mobiltelefonnummer des Benutzers',
                'placeholder' => 'Mobiltelefonnummer des Benutzers',
            ],
            'user_email' => [
                'label'       => 'E-Mail-Adresse des Benutzers',
                'placeholder' => 'E-Mail-Adresse des Benutzers',
            ],
            'password' => [
                'label'       => 'Passwort des Benutzers',
                'placeholder' => 'Passwort des Benutzers',
            ],
            'user_web' => [
                'label'       => 'Webseite des Benutzers',
                'placeholder' => 'Webseite des Benutzers',
            ],
            'user_vat_id' => [
                'label'       => 'Umsatzsteuer-ID des Benutzers',
                'placeholder' => 'Umsatzsteuer-ID des Benutzers',
            ],
            'user_tax_code' => [
                'label'       => 'Steuernummer des Benutzers',
                'placeholder' => 'Steuernummer des Benutzers',
            ],
            'user_psalt' => [
                'label'       => 'Passwort-Salt des Benutzers',
                'placeholder' => 'Passwort-Salt des Benutzers',
            ],
            'user_all_clients' => [
                'label'       => 'Alle Kunden für den Benutzer',
                'placeholder' => 'Alle Kunden für den Benutzer',
            ],
            'user_passwordreset_token' => [
                'label'       => 'Token zum Zurücksetzen des Benutzerpassworts',
                'placeholder' => 'Token zum Zurücksetzen des Benutzerpassworts',
            ],
            'user_subscribernumber' => [
                'label'       => 'Teilnehmernummer des Benutzers',
                'placeholder' => 'Teilnehmernummer des Benutzers',
            ],
            'user_iban' => [
                'label'       => 'IBAN des Benutzers',
                'placeholder' => 'IBAN des Benutzers',
            ],
            'user_gln' => [
                'label'       => 'GLN des Benutzers',
                'placeholder' => 'GLN des Benutzers',
            ],
            'user_rcc' => [
                'label'       => 'RCC des Benutzers',
                'placeholder' => 'RCC des Benutzers',
            ],
        ],
    ],
    'invoice_items' => [
        'inputs' => [
            'invoice_id' => [
                'label'       => 'Rechnungs-ID',
                'placeholder' => 'Rechnungs-ID',
            ],
            'item_tax_rate_id' => [
                'label'       => 'Steuersatz-ID des Postens',
                'placeholder' => 'Steuersatz-ID des Postens',
            ],
            'product_id' => [
                'label'       => 'Produkt-ID des Postens',
                'placeholder' => 'Produkt-ID des Postens',
            ],
            'item_date_added' => [
                'label'       => 'Erstellungsdatum des Postens',
                'placeholder' => 'Erstellungsdatum des Postens',
            ],
            'item_task_id' => [
                'label'       => 'Aufgaben-ID des Postens',
                'placeholder' => 'Aufgaben-ID des Postens',
            ],
            'item_name' => [
                'label'       => 'Bezeichnung des Postens',
                'placeholder' => 'Bezeichnung des Postens',
            ],
            'item_description' => [
                'label'       => 'Beschreibung des Postens',
                'placeholder' => 'Beschreibung des Postens',
            ],
            'item_quantity' => [
                'label'       => 'Menge des Postens',
                'placeholder' => 'Menge des Postens',
            ],
            'item_price' => [
                'label'       => 'Preis des Postens',
                'placeholder' => 'Preis des Postens',
            ],
            'item_discount_amount' => [
                'label'       => 'Rabattbetrag des Postens',
                'placeholder' => 'Rabattbetrag des Postens',
            ],
            'item_order' => [
                'label'       => 'Reihenfolge des Postens',
                'placeholder' => 'Reihenfolge des Postens',
            ],
            'item_is_recurring' => [
                'label'       => 'Wiederkehrender Posten',
                'placeholder' => 'Wiederkehrender Posten',
            ],
            'item_product_unit' => [
                'label'       => 'Produkteinheit des Postens',
                'placeholder' => 'Produkteinheit des Postens',
            ],
            'item_unit_id' => [
                'label'       => 'Einheiten-ID des Postens',
                'placeholder' => 'Einheiten-ID des Postens',
            ],
            'item_date' => [
                'label'       => 'Datum des Postens',
                'placeholder' => 'Datum des Postens',
            ],
            'tax_rate_id' => [
                'label'       => 'Steuersatz-ID',
                'placeholder' => 'Steuersatz-ID',
            ],
        ],
        'itemTitle'       => 'Rechnungsposten',
        'collectionTitle' => 'Rechnungsposten',
    ],
    'invoice_groups' => [
        'inputs' => [
            'document_group_name' => [
                'label'       => 'Name des Rechnungskreises',
                'placeholder' => 'Name des Rechnungskreises',
            ],
            'invoice_group_identifier_format' => [
                'label'       => 'Nummernformat des Rechnungskreises',
                'placeholder' => 'Nummernformat des Rechnungskreises',
            ],
            'invoice_group_next_id' => [
                'label'       => 'Nächste Nummer des Rechnungskreises',
                'placeholder' => 'Nächste Nummer des Rechnungskreises',
            ],
            'invoice_group_left_pad' => [
                'label'       => 'Führende Nullen des Rechnungskreises',
                'placeholder' => 'Führende Nullen des Rechnungskreises',
            ],
        ],
        'itemTitle'       => 'Rechnungskreis',
        'collectionTitle' => 'Rechnungskreise',
    ],
    'projects' => [
        'itemTitle'       => 'Projekt',
        'collectionTitle' => 'Projekte',
    ],
    'tax_rates' => [
        'itemTitle'       => 'Steuersatz',
        'collectionTitle' => 'Steuersätze',
        'inputs'          => [
            'tax_rate_name' => [
                'label'       => 'Bezeichnung des Steuersatzes',
                'placeholder' => 'Bezeichnung des Steuersatzes',
            ],
            'tax_rate_percent' => [
                'label'       => 'Steuersatz (Prozent)',
                'placeholder' => 'Steuersatz (Prozent)',
            ],
        ],
    ],
    'quote_items' => [
        'inputs' => [
            'quote_id' => [
                'label'       => 'Angebots-ID',
                'placeholder' => 'Angebots-ID',
            ],
            'item_tax_rate_id' => [
                'label'       => 'Steuersatz-ID des Postens',
                'placeholder' => 'Steuersatz-ID des Postens',
            ],
            'item_date_added' => [
                'label'       => 'Erstellungsdatum des Postens',
                'placeholder' => 'Erstellungsdatum des Postens',
            ],
            'item_name' => [
                'label'       => 'Bezeichnung des Postens',
                'placeholder' => 'Bezeichnung des Postens',
            ],
            'item_description' => [
                'label'       => 'Beschreibung des Postens',
                'placeholder' => 'Beschreibung des Postens',
            ],
            'item_quantity' => [
                'label'       => 'Menge des Postens',
                'placeholder' => 'Menge des Postens',
            ],
            'item_price' => [
                'label'       => 'Preis des Postens',
                'placeholder' => 'Preis des Postens',
            ],
            'item_discount_amount' => [
                'label'       => 'Rabattbetrag des Postens',
                'placeholder' => 'Rabattbetrag des Postens',
            ],
            'item_order' => [
                'label'       => 'Reihenfolge des Postens',
                'placeholder' => 'Reihenfolge des Postens',
            ],
            'item_product_unit' => [
                'label'       => 'Produkteinheit des Postens',
                'placeholder' => 'Produkteinheit des Postens',
            ],
            'item_unit_id' => [
                'label'       => 'Einheiten-ID des Postens',
                'placeholder' => 'Einheiten-ID des Postens',
            ],
            'tax_rate_id' => [
                'label'       => 'Steuersatz-ID',
                'placeholder' => 'Steuersatz-ID',
            ],
            'product_id' => [
                'label'       => 'Produkt-ID',
                'placeholder' => 'Produkt-ID',
            ],
        ],
        'itemTitle'       => 'Angebotsposten',
        'collectionTitle' => 'Angebotsposten',
    ],
    'quote_amounts' => [
        'inputs' => [
            'quote_id' => [
                'label'       => 'Angebots-ID',
                'placeholder' => 'Angebots-ID',
            ],
            'quote_item_subtotal' => [
                'label'       => 'Zwischensumme der Angebotsposten',
                'placeholder' => 'Zwischensumme der Angebotsposten',
            ],
            'quote_item_tax_total' => [
                'label'       => 'Steuersumme der Angebotsposten',
                'placeholder' => 'Steuersumme der Angebotsposten',
            ],
            'quote_tax_total' => [
                'label'       => 'Steuersumme des Angebots',
                'placeholder' => 'Steuersumme des Angebots',
            ],
            'quote_total' => [
                'label'       => 'Angebotssumme',
                'placeholder' => 'Angebotssumme',
            ],
        ],
        'itemTitle'       => 'Angebotsbetrag',
        'collectionTitle' => 'Angebotsbeträge',
    ],
    'quote_item_amounts' => [
        'inputs' => [
            'item_id' => [
                'label'       => 'Posten-ID',
                'placeholder' => 'Posten-ID',
            ],
            'item_subtotal' => [
                'label'       => 'Zwischensumme des Postens',
                'placeholder' => 'Zwischensumme des Postens',
            ],
            'item_tax_total' => [
                'label'       => 'Steuersumme des Postens',
                'placeholder' => 'Steuersumme des Postens',
            ],
            'item_discount' => [
                'label'       => 'Postenrabatt',
                'placeholder' => 'Postenrabatt',
            ],
            'item_total' => [
                'label'       => 'Gesamtbetrag des Postens',
                'placeholder' => 'Gesamtbetrag des Postens',
            ],
            'product_id' => [
                'label'       => 'Produkt-ID',
                'placeholder' => 'Produkt-ID',
            ],
        ],
        'itemTitle'       => 'Betrag des Angebotspostens',
        'collectionTitle' => 'Beträge der Angebotsposten',
    ],
];
