<?php

return [
    'invoice_sent_subject'     => 'Invoice #{{ invoice.number }}',
    'invoice_sent_body'        => 'Please find your invoice #{{ invoice.number }} attached.',
    'invoice_reminder_subject' => 'Payment reminder — Invoice #{{ invoice.number }}',
    'invoice_reminder_body'    => 'Dear {{ customer.name }},

This is a friendly reminder that invoice #{{ invoice.number }} for {{ invoice.total_formatted }} was due on {{ invoice.due_date_formatted }} and remains unpaid.

Please arrange payment at your earliest convenience. The invoice is attached for your reference.

Thank you,
{{ company.name }}',
];
