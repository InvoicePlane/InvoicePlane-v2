<?php

return [
    'invoice_sent_subject'     => 'Rechnung #{{ invoice.number }}',
    'invoice_sent_body'        => 'Anbei erhalten Sie Ihre Rechnung #{{ invoice.number }}.',
    'invoice_reminder_subject' => 'Zahlungserinnerung — Rechnung #{{ invoice.number }}',
    'invoice_reminder_body'    => 'Guten Tag {{ customer.name }},

wir möchten Sie daran erinnern, dass die Rechnung #{{ invoice.number }} über {{ invoice.total_formatted }} am {{ invoice.due_date_formatted }} fällig war und noch nicht bezahlt wurde.

Bitte veranlassen Sie die Zahlung zeitnah. Die Rechnung finden Sie zur Übersicht im Anhang.

Vielen Dank
{{ company.name }}',
];
