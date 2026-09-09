<?php

namespace Modules\Quotes\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Clients\Enums\CommunicationType;
use Modules\Core\Enums\MailType;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\Setting;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Support\EmailTemplatePreview;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Mail\QuoteMailable;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteSignature;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class QuoteService extends BaseService
{
    /**
     * Title of the EmailTemplate used as the company's quote email template.
     */
    public const QUOTE_EMAIL_TEMPLATE_TITLE = 'quote_sent';

    public function model(): string
    {
        return Quote::class;
    }

    public function createQuote(array $data): Model
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal  = $this->calculateItemTaxTotal($data);
            $quoteTaxTotal = $this->calculateQuoteTaxTotal($data);
            $quoteTotal    = $this->calculateQuoteTotal($data, $itemTaxTotal, $quoteTaxTotal);

            $quote = Quote::query()->create([
                'company_id'             => $this->getCompanyId(),
                'prospect_id'            => $data['prospect_id'],
                'numbering_id'           => $data['numbering_id'] ?? null,
                'user_id'                => $data['user_id'] ?? auth()->id(),
                'quote_number'           => $data['quote_number'],
                'client_reference'       => $data['client_reference'] ?? null,
                'work_order'             => $data['work_order'] ?? null,
                'quote_status'           => $data['quote_status'],
                'quoted_at'              => Carbon::parse($data['quoted_at']),
                'quote_expires_at'       => Carbon::parse($data['quote_expires_at']),
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $itemTaxTotal,
                'quote_item_subtotal'    => $data['quote_item_subtotal'] ?? 0,
                'quote_tax_total'        => $quoteTaxTotal,
                'quote_total'            => $data['quote_total'] ?? 0,
                'quote_password'         => $data['quote_password'] ?? null,
                'url_key'                => $data['url_key'] ?? Str::random(32),
                'template'               => $data['template'] ?? null,
                'summary'                => $data['summary'] ?? null,
                'terms'                  => $data['terms'] ?? null,
                'footer'                 => $data['footer'] ?? null,
            ]);

            foreach ($data['quoteItems'] as $item) {
                $calculateMySubtotal = $item['quantity'] * $item['price'];

                $quote->quoteItems()->create([
                    'company_id'      => $this->getCompanyId(),
                    'product_id'      => $item['product_id'] ?? 1,
                    'product_unit_id' => $item['product_unit_id'] ?? 1,
                    'added_at'        => Carbon::now()->toDateString(),
                    'item_name'       => $item['item_name'] ?? null,
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                    'discount'        => $item['discount'] ?? 0,
                    'tax_1'           => $item['tax_1'] ?? 0,
                    'tax_2'           => $item['tax_2'] ?? 0,
                    'tax_total'       => $item['tax_total'] ?? 0,
                    'total'           => $item['total'] ?? 0,
                    'tax_rate_id'     => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id'   => $item['tax_rate_2_id'] ?? null,
                    'display_order'   => 1,
                    'description'     => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            return $quote;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateQuote(Quote $quote, array $data): Quote
    {
        $itemTaxTotal  = $this->calculateItemTaxTotal($data);
        $quoteTaxTotal = $this->calculateQuoteTaxTotal($data);
        $quoteTotal    = $this->calculateQuoteTotal($data, $itemTaxTotal, $quoteTaxTotal);

        DB::beginTransaction();

        try {
            $quote->update([
                'prospect_id'            => $data['prospect_id'],
                'client_reference'       => $data['client_reference'] ?? null,
                'work_order'             => $data['work_order'] ?? null,
                'quoted_at'              => $data['quoted_at'],
                'quote_expires_at'       => $data['quote_expires_at'],
                'quote_status'           => $data['quote_status'],
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $itemTaxTotal,
                'quote_item_subtotal'    => $data['quote_item_subtotal'] ?? 0,
                'quote_tax_total'        => $quoteTaxTotal,
                'quote_total'            => $quoteTotal,
                'summary'                => $data['summary'] ?? null,
            ]);

            DB::commit();

            return $quote->refresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function duplicateQuote(Quote $quote): Quote
    {
        DB::beginTransaction();

        try {
            $copy = Quote::query()->create([
                'company_id'             => $quote->company_id,
                'prospect_id'            => $quote->prospect_id,
                'numbering_id'           => $quote->numbering_id,
                'user_id'                => auth()->id(),
                'quote_number'           => null,
                'quote_status'           => QuoteStatus::DRAFT,
                'quoted_at'              => Carbon::today(),
                'quote_expires_at'       => Carbon::today()->addDays(30),
                'quote_discount_amount'  => $quote->quote_discount_amount,
                'quote_discount_percent' => $quote->quote_discount_percent,
                'item_tax_total'         => $quote->item_tax_total,
                'quote_item_subtotal'    => $quote->quote_item_subtotal,
                'quote_tax_total'        => $quote->quote_tax_total,
                'quote_total'            => $quote->quote_total,
                'quote_password'         => null,
                'url_key'                => Str::random(32),
                'template'               => $quote->template,
                'summary'                => $quote->summary,
                'terms'                  => $quote->terms,
                'footer'                 => $quote->footer,
            ]);

            foreach ($quote->quoteItems as $item) {
                $copy->quoteItems()->create([
                    'company_id'      => $item->company_id,
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'added_at'        => Carbon::today()->toDateString(),
                    'item_name'       => $item->item_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'discount'        => $item->discount,
                    'subtotal'        => $item->subtotal,
                    'tax_1'           => $item->tax_1,
                    'tax_2'           => $item->tax_2,
                    'tax_total'       => $item->tax_total,
                    'total'           => $item->total,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                    'display_order'   => $item->display_order,
                    'description'     => $item->description,
                ]);
            }

            DB::commit();

            return $copy;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteQuote(Quote $quote): Quote
    {
        DB::beginTransaction();
        try {
            $quote->quoteItems()->delete();
            $quote->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $quote;
    }

    /**
     * Convert an accepted quote into a draft invoice.
     *
     * Copies the client, all quote items, and the summary fields onto a new
     * unnumbered draft invoice, then marks the quote as Converted. Numbering
     * is assigned when the invoice leaves draft.
     *
     * @throws InvalidArgumentException when the quote was already converted
     */
    public function convertQuoteToInvoice(Quote $quote): Invoice
    {
        if ($quote->quote_status === QuoteStatus::CONVERTED) {
            throw new InvalidArgumentException(trans('ip.quote_already_converted'));
        }

        return DB::transaction(function () use ($quote) {
            $invoice = Invoice::query()->create([
                'company_id'               => $quote->company_id,
                'customer_id'              => $quote->prospect_id,
                'numbering_id'             => null,
                'user_id'                  => auth()->id() ?? $quote->user_id,
                'invoice_number'           => null,
                'invoice_status'           => InvoiceStatus::DRAFT->value,
                'invoice_sign'             => '1',
                'invoiced_at'              => Carbon::today(),
                'invoice_due_at'           => Carbon::today()->addDays(30),
                'invoice_discount_amount'  => $quote->quote_discount_amount ?? 0,
                'invoice_discount_percent' => $quote->quote_discount_percent ?? 0,
                'item_tax_total'           => $quote->item_tax_total ?? 0,
                'invoice_item_subtotal'    => $quote->quote_item_subtotal ?? 0,
                'invoice_tax_total'        => $quote->quote_tax_total ?? 0,
                'invoice_total'            => $quote->quote_total ?? 0,
                'url_key'                  => Str::random(32),
                'summary'                  => $quote->summary,
                'terms'                    => $quote->terms,
                'footer'                   => $quote->footer,
            ]);

            foreach ($quote->quoteItems as $item) {
                $invoice->invoiceItems()->create([
                    'company_id'      => $item->company_id,
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'task_id'         => $item->task_id,
                    'added_at'        => Carbon::today()->toDateString(),
                    'item_name'       => $item->item_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'discount'        => $item->discount,
                    'subtotal'        => $item->subtotal,
                    'tax_1'           => $item->tax_1,
                    'tax_2'           => $item->tax_2,
                    'tax_total'       => $item->tax_total,
                    'total'           => $item->total,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                    'description'     => $item->description,
                ]);
            }

            $quote->update(['quote_status' => QuoteStatus::CONVERTED->value]);

            return $invoice;
        });
    }

    /**
     * Resolve the recipient/subject/body defaults for the "Email Quote" modal,
     * rendering the company's quote email template against this quote.
     */
    public function resolveEmailDefaults(Quote $quote): array
    {
        $defaults = $this->resolveTemplateDefaults($quote);
        unset($defaults['template']);

        return $defaults;
    }

    /**
     * Queue the quote mailable for delivery using the given (possibly
     * user-edited) recipient/subject/body, as resolved/prefilled by
     * resolveEmailDefaults() and submitted via the "Email Quote" modal.
     * Logs a MailQueue entry so the quote's send history is auditable, and
     * transitions a draft quote to Sent on first send.
     */
    public function sendQuoteEmail(Quote $quote, string $recipient, string $subject, string $body): void
    {
        Mail::to($recipient)
            ->cc($this->resolveQuoteCcEmails($quote))
            ->queue(new QuoteMailable($quote, $subject, $body));

        $template = EmailTemplate::forCompany($quote->company_id)
            ->where('title', self::QUOTE_EMAIL_TEMPLATE_TITLE)
            ->first();

        $quote->mailQueue()->create([
            'mailable_type' => Quote::class,
            'type'          => MailType::SENT,
            'from'          => $template?->from_email ?? (string) config('mail.from.address'),
            'to'            => $recipient,
            'cc'            => '',
            'bcc'           => '',
            'subject'       => $subject,
            'body'          => $body,
            'attach_pdf'    => true,
            'is_sent'       => true,
            'sent_at'       => now(),
        ]);

        if ($quote->quote_status === QuoteStatus::DRAFT) {
            $quote->update(['quote_status' => QuoteStatus::SENT]);
        }
    }

    /**
     * Capture a client (or user-linked) signature against a quote, storing
     * the decoded image on the configured Filament filesystem disk and
     * recording a QuoteSignature row. Does not change the quote's status.
     *
     * @throws InvalidArgumentException when $signatureData isn't a valid base64 data URL
     */
    public function captureSignature(
        Quote $quote,
        string $signatureData,
        string $signerName,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): QuoteSignature {
        [$extension, $binary] = $this->decodeSignatureData($signatureData);

        $disk = config('filament.default_filesystem_disk');
        $path = 'quote-signatures/' . $quote->id . '/' . Str::random(40) . '.' . $extension;

        if ( ! Storage::disk($disk)->put($path, $binary)) {
            throw new RuntimeException(trans('ip.quote_signature_storage_failed'));
        }

        try {
            return $quote->signatures()->create([
                'company_id'     => $quote->company_id,
                'user_id'        => $userId,
                'signer_name'    => $signerName,
                'signature_disk' => $disk,
                'signature_path' => $path,
                'signed_at'      => now(),
                'ip_address'     => $ipAddress,
                'user_agent'     => $userAgent === null ? null : Str::limit($userAgent, 255, ''),
            ]);
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($path);
            throw $e;
        }
    }

    /**
     * Render the quote document markup used by both the PDF driver and the
     * on-screen preview.
     *
     * dompdf can read local filesystem paths directly, but a browser
     * rendering the guest preview can't — pass $forBrowser to swap embedded
     * images for the guest routes that stream them instead.
     */
    public function renderHtml(Quote $quote, bool $forBrowser = false): string
    {
        $quote->loadMissing(['company', 'prospect', 'quoteItems', 'signatures']);

        return view('quotes::pdf.quote', [
            'quote'      => $quote,
            'branding'   => $this->resolveBranding($quote, $forBrowser),
            'signatures' => $this->resolveSignatureImages($quote, $forBrowser),
        ])->render();
    }

    /**
     * Stream the quote as a PDF download named after the quote number.
     */
    public function generatePdf(Quote $quote): StreamedResponse
    {
        $driver   = PDFFactory::create();
        $output   = $driver->getOutput($this->renderHtml($quote));
        $filename = ($quote->quote_number ?: 'quote-draft-' . $quote->id) . '.pdf';

        return response()->streamDownload(
            function () use ($output): void {
                echo $output;
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Resolve the company's invoice logo to a [disk, path] pair, or null
     * when no logo is set or the stored file is missing. Used both to embed
     * the logo in the PDF and to serve it via the guest logo route.
     *
     * @return array{disk: string, path: string}|null
     */
    public function resolveLogoPath(Quote $quote): ?array
    {
        $disk = config('filament.default_filesystem_disk');
        $path = Setting::getForCompany($quote->company_id, Setting::KEY_INVOICE_LOGO);

        if ( ! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return ['disk' => $disk, 'path' => $path];
    }

    /**
     * Company branding for the quote PDF/preview: colors, font, and logo.
     * Falls back to the current hardcoded look when a company hasn't set
     * any branding.
     *
     * @return array{primary_color: string, accent_color: string, font_family: string, font_size: string, logo_path: ?string}
     */
    private function resolveBranding(Quote $quote, bool $forBrowser): array
    {
        $companyId = $quote->company_id;
        $logo      = $this->resolveLogoPath($quote);

        return [
            'primary_color' => Setting::getForCompany($companyId, Setting::KEY_PRIMARY_COLOR) ?: '#1f2937',
            'accent_color'  => Setting::getForCompany($companyId, Setting::KEY_ACCENT_COLOR) ?: '#6b7280',
            'font_family'   => Setting::getForCompany($companyId, Setting::KEY_FONT_FAMILY) ?: 'DejaVu Sans, Helvetica, Arial, sans-serif',
            'font_size'     => Setting::getForCompany($companyId, Setting::KEY_FONT_SIZE) ?: '12',
            'logo_path'     => $logo === null
                ? null
                : ($forBrowser ? route('quotes.guest.logo', $quote) : Storage::disk($logo['disk'])->path($logo['path'])),
        ];
    }

    /**
     * Resolve each signature's stored image to a src usable in an <img> tag
     * — an absolute filesystem path for the PDF driver, or a guest route
     * URL for the browser preview — skipping any whose file is missing.
     *
     * @return array<int, array{signer_name: string, signed_at: ?\Illuminate\Support\Carbon, path: string}>
     */
    private function resolveSignatureImages(Quote $quote, bool $forBrowser): array
    {
        return $quote->signatures
            ->map(function (QuoteSignature $signature) use ($quote, $forBrowser): ?array {
                $disk = Storage::disk($signature->signature_disk);

                if ( ! $disk->exists($signature->signature_path)) {
                    return null;
                }

                return [
                    'signer_name' => $signature->signer_name,
                    'signed_at'   => $signature->signed_at,
                    'path'        => $forBrowser
                        ? route('quotes.guest.signature', [$quote, $signature])
                        : $disk->path($signature->signature_path),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Decode a `data:image/<type>;base64,<payload>` string into a
     * [file extension, raw binary] pair.
     *
     * @return array{0: string, 1: string}
     *
     * @throws InvalidArgumentException when the input isn't a valid base64 image data URL
     */
    private function decodeSignatureData(string $signatureData): array
    {
        $mimeToExtension = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
        ];

        if (
            ! preg_match('/^data:(image\/(?:png|jpeg|webp));base64,(?<payload>.+)$/', $signatureData, $matches)
            || ($binary = base64_decode($matches['payload'], true)) === false
        ) {
            throw new InvalidArgumentException(trans('ip.quote_signature_invalid_format'));
        }

        $detectedImage = @getimagesizefromstring($binary);

        if ($detectedImage === false || $detectedImage['mime'] !== $matches[1]) {
            throw new InvalidArgumentException(trans('ip.quote_signature_invalid_format'));
        }

        return [$mimeToExtension[$matches[1]], $binary];
    }

    /**
     * Shared resolution logic for the "Email Quote" modal: loads the
     * company's quote EmailTemplate, renders its subject/body against this
     * quote, and resolves the recipient. Returns the EmailTemplate alongside
     * the rendered defaults so callers that also need template fields (e.g.
     * sendQuoteEmail()'s from_email) don't have to re-query it.
     */
    private function resolveTemplateDefaults(Quote $quote): array
    {
        $quote->loadMissing(['prospect', 'company']);

        $template = EmailTemplate::forCompany($quote->company_id)
            ->where('title', self::QUOTE_EMAIL_TEMPLATE_TITLE)
            ->first();

        $placeholders = [
            'quote.number'               => $quote->quote_number,
            'quote.total_formatted'      => number_format((float) $quote->quote_total, 2),
            'quote.expires_at_formatted' => DateHelpers::formatDate($quote->quote_expires_at),
            'customer.name'              => $quote->prospect?->company_name,
            'company.name'               => $quote->company?->name,
        ];

        $defaultSubject = trans('ip.email_quote_default_subject', ['number' => $quote->quote_number]);

        return [
            'template'  => $template,
            'recipient' => $this->resolveQuoteRecipientEmail($quote),
            'subject'   => $template?->subject
                ? EmailTemplatePreview::render($template->subject, $placeholders)
                : $defaultSubject,
            'body' => $template?->body
                ? EmailTemplatePreview::render($template->body, $placeholders)
                : '',
        ];
    }

    /**
     * Walk the quote's prospect → contacts → communications chain and
     * return the first email address found, preferring a primary one.
     */
    private function resolveQuoteRecipientEmail(Quote $quote): ?string
    {
        $quote->loadMissing('prospect.contacts.communications');

        $prospect = $quote->prospect;

        if ( ! $prospect) {
            return null;
        }

        $emailCommunication = $prospect->contacts
            ->flatMap(fn ($contact) => $contact->communications)
            ->filter(fn ($communication) => $communication->communication_type === CommunicationType::EMAIL->value)
            ->sortByDesc('is_primary')
            ->first();

        return $emailCommunication?->communication_value;
    }

    /**
     * Merge the prospect's stored CC addresses with the quote email
     * template's cc column (comma/semicolon separated), validating each
     * address and de-duplicating the result.
     */
    private function resolveQuoteCcEmails(Quote $quote): array
    {
        $quote->loadMissing('prospect');

        $prospectCcEmails = $quote->prospect?->ccEmailCommunications()
            ->pluck('communication_value')
            ->all() ?? [];

        $template = EmailTemplate::forCompany($quote->company_id)
            ->where('title', self::QUOTE_EMAIL_TEMPLATE_TITLE)
            ->first();

        $templateCcEmails = $template?->cc
            ? preg_split('/[,;]+/', $template->cc)
            : [];

        return collect([...$prospectCcEmails, ...$templateCcEmails])
            ->map(fn (string $email) => mb_trim($email))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    private function calculateItemTaxTotal(array $data): float
    {
        return collect($data['quoteItems'] ?? [])->sum(fn ($item) => $item['tax_total'] ?? 0);
    }

    private function calculateQuoteTaxTotal(array $data): float
    {
        return collect($data['quoteItems'] ?? [])->sum(fn ($item) => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0));
    }

    private function calculateQuoteTotal(array $data, float $itemTaxTotal, float $quoteTaxTotal): float
    {
        $subtotal       = $data['quote_item_subtotal'] ?? 0;
        $discountAmount = $data['quote_discount_amount'] ?? 0;

        return $subtotal + $itemTaxTotal + $quoteTaxTotal - $discountAmount;
    }
}
