<?php

namespace Modules\Core\Tests\Feature;

use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Services\EmailTemplateVariableResolver;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Test;

class EmailTemplateVariablesTest extends AbstractCompanyPanelTestCase
{
    protected EmailTemplateVariableResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new EmailTemplateVariableResolver();
    }

    #[Test]
    public function it_resolves_the_invoicing_contact_marked_as_default_recipient(): void
    {
        /* Arrange */
        $client = $this->makeClient();
        $this->makeContact($client, ['first_name' => 'Paula', 'last_name' => 'Primary'], 'paula@acme.test');
        $this->makeContact($client, ['first_name' => 'Fiona', 'last_name' => 'Finance', 'default_to' => true], 'finance@acme.test');

        /* Act */
        $resolved = $this->resolver->resolve(
            'Dear {{invoicing_contact_name}} <{{invoicing_contact_email}}>',
            $this->makeInvoice($client),
        );

        /* Assert */
        $this->assertSame('Dear Fiona Finance <finance@acme.test>', $resolved);
    }

    #[Test]
    public function it_falls_back_to_the_primary_contact_when_no_invoicing_contact_exists(): void
    {
        /* Arrange */
        $client = $this->makeClient();
        $this->makeContact($client, ['first_name' => 'Casual', 'last_name' => 'Contact'], 'casual@acme.test');
        $primary = $this->makeContact($client, ['first_name' => 'Paula', 'last_name' => 'Primary'], 'paula@acme.test');
        $client->update(['primary_contact_id' => $primary->id]);

        /* Act */
        $resolved = $this->resolver->resolve('{{invoicing_contact_email}}', $this->makeInvoice($client));

        /* Assert */
        $this->assertSame('paula@acme.test', $resolved);
    }

    #[Test]
    public function it_falls_back_to_the_first_contact_when_nothing_is_marked(): void
    {
        /* Arrange */
        $client = $this->makeClient();
        $this->makeContact($client, ['first_name' => 'Only', 'last_name' => 'One'], 'only@acme.test');

        /* Act */
        $resolved = $this->resolver->resolve('{{invoicing_contact_name}}', $this->makeInvoice($client));

        /* Assert */
        $this->assertSame('Only One', $resolved);
    }

    #[Test]
    public function it_resolves_document_client_and_company_variables_for_invoices(): void
    {
        /* Arrange */
        $client = $this->makeClient();

        /* Act */
        $resolved = $this->resolver->resolve(
            '{{document_number}} for {{client_name}} from {{company_name}}: {{document_total}} ({{document_date}})',
            $this->makeInvoice($client),
        );

        /* Assert */
        $this->assertSame(
            'INV-VAR-1 for ACME Ltd from ' . $this->company->name . ': 250.00 (2026-01-01)',
            $resolved,
        );
    }

    #[Test]
    public function it_resolves_variables_for_quotes(): void
    {
        /* Arrange */
        $client = $this->makeClient();
        $this->makeContact($client, ['first_name' => 'Fiona', 'last_name' => 'Finance', 'default_to' => true], 'finance@acme.test');

        $quote = Quote::factory()->create([
            'company_id'   => $this->company->id,
            'prospect_id'  => $client->id,
            'user_id'      => $this->user->id,
            'quote_number' => 'Q-VAR-1',
            'quote_total'  => 99,
        ]);

        /* Act */
        $resolved = $this->resolver->resolve('{{document_number}} to {{invoicing_contact_email}}', $quote);

        /* Assert */
        $this->assertSame('Q-VAR-1 to finance@acme.test', $resolved);
    }

    #[Test]
    public function it_leaves_unknown_variables_untouched(): void
    {
        /* Arrange */
        $client = $this->makeClient();

        /* Act */
        $resolved = $this->resolver->resolve('Hello {{no_such_variable}}', $this->makeInvoice($client));

        /* Assert */
        $this->assertSame('Hello {{no_such_variable}}', $resolved);
    }

    #[Test]
    public function it_lists_the_invoicing_contact_variables_in_the_available_set(): void
    {
        /* Act */
        $variables = $this->resolver->variables();

        /* Assert */
        $this->assertArrayHasKey('{{invoicing_contact_name}}', $variables);
        $this->assertArrayHasKey('{{invoicing_contact_email}}', $variables);
    }

    protected function makeClient(): Relation
    {
        $client = Relation::factory()->create([
            'company_id'   => $this->company->id,
            'company_name' => 'ACME Ltd',
        ]);

        $client->update(['primary_contact_id' => null]);
        $client->contacts()->delete();

        /** @var Relation $fresh */
        $fresh = $client->fresh();

        return $fresh;
    }

    protected function makeContact(Relation $client, array $attributes, ?string $email = null): Contact
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create(array_merge([
            'company_id'  => $this->company->id,
            'relation_id' => $client->id,
            'default_to'  => false,
        ], $attributes));

        if ($email !== null) {
            $contact->communications()->create([
                'company_id'          => $this->company->id,
                'communication_type'  => CommunicationType::EMAIL->value,
                'communication_value' => $email,
                'is_primary'          => true,
            ]);
        }

        return $contact;
    }

    protected function makeInvoice(Relation $client): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'company_id'     => $this->company->id,
            'customer_id'    => $client->id,
            'user_id'        => $this->user->id,
            'invoice_number' => 'INV-VAR-1',
            'invoiced_at'    => '2026-01-01',
            'invoice_total'  => 250,
        ]);

        return $invoice;
    }
}
