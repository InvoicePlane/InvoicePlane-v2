<?php

namespace Modules\Invoices\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;

class InvoiceReminderMailable extends Mailable implements ShouldQueue
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

    /**
     * Renders the PDF here, at send time, rather than in the caller ahead of
     * queuing — a pre-rendered binary on a ShouldQueue mailable would get
     * serialized into the queue payload (DB row / Redis value / SQS message)
     * instead of being generated when the job actually runs.
     */
    public function attachments(): array
    {
        $filename  = ($this->invoice->invoice_number ?: 'invoice-' . $this->invoice->id) . '.pdf';
        $html      = app(InvoiceService::class)->renderHtml($this->invoice);
        $pdfBinary = PDFFactory::create()->getOutput($html);

        return [
            Attachment::fromData(fn () => $pdfBinary, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
