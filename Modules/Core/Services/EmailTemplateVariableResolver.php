<?php

namespace Modules\Core\Services;

use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

/**
 * Substitutes {{variable}} tags in email template text with document data.
 *
 * The invoicing contact (#363) is the relation's contact flagged as the
 * default "To" recipient; when none exists it falls back to the relation's
 * primary contact, then to the first contact, so existing templates keep
 * working for clients without a dedicated invoicing contact.
 */
class EmailTemplateVariableResolver
{
    /**
     * Tag => description, for the template editor's variable reference.
     *
     * @return array<string, string>
     */
    public function variables(): array
    {
        return [
            '{{client_name}}'             => trans('ip.variable_client_name'),
            '{{company_name}}'            => trans('ip.variable_company_name'),
            '{{document_number}}'         => trans('ip.variable_document_number'),
            '{{document_date}}'           => trans('ip.variable_document_date'),
            '{{document_total}}'          => trans('ip.variable_document_total'),
            '{{invoicing_contact_name}}'  => trans('ip.variable_invoicing_contact_name'),
            '{{invoicing_contact_email}}' => trans('ip.variable_invoicing_contact_email'),
        ];
    }

    public function resolve(string $text, Invoice | Quote $document): string
    {
        $isInvoice = $document instanceof Invoice;
        $client    = $isInvoice ? $document->customer : $document->prospect;
        $contact   = $client ? $this->invoicingContact($client) : null;

        $values = [
            '{{client_name}}'     => (string) ($client?->company_name ?? ''),
            '{{company_name}}'    => (string) ($document->company?->name ?? ''),
            '{{document_number}}' => (string) ($isInvoice ? $document->invoice_number : $document->quote_number),
            '{{document_date}}'   => (string) ($isInvoice
                ? $document->invoiced_at?->format('Y-m-d')
                : $document->quoted_at?->format('Y-m-d')),
            '{{document_total}}'          => number_format((float) ($isInvoice ? $document->invoice_total : $document->quote_total), 2, '.', ''),
            '{{invoicing_contact_name}}'  => (string) ($contact?->full_name ?? ''),
            '{{invoicing_contact_email}}' => $this->invoicingContactEmail($client, $contact),
        ];

        return strtr($text, $values);
    }

    public function invoicingContact(Relation $client): ?Contact
    {
        $contacts = $client->contacts()->with('communications')->get();

        return $contacts->firstWhere('default_to', true)
            ?? ($client->primary_contact_id ? $contacts->firstWhere('id', $client->primary_contact_id) : null)
            ?? $contacts->first();
    }

    protected function invoicingContactEmail(?Relation $client, ?Contact $contact): string
    {
        $email = $contact ? $this->emailOf($contact) : '';

        if ($email === '' && $client !== null) {
            $email = $this->emailOf($client->loadMissing('communications'));
        }

        return $email;
    }

    protected function emailOf($model): string
    {
        $communication = $model->communications
            ->first(function ($entry): bool {
                $type = $entry->communication_type;

                return ($type instanceof CommunicationType ? $type->value : (string) $type) === CommunicationType::EMAIL->value;
            });

        return (string) ($communication?->communication_value ?? '');
    }
}
