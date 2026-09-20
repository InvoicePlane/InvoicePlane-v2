<?php

namespace Modules\Invoices\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Invoices\Models\Invoice;

class InvoiceMailable extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $emailSubject,
        public string $bodyText,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e($this->bodyText)),
        );
    }
}
