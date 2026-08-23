<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\MailType;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\EditQuote;
use Modules\Quotes\Mail\QuoteMailable;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class EmailQuoteActionTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Resource pages gate on Spatie permissions, so the test user
         * needs the seeded client_admin permission set to mount the page.
         */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_prefills_the_modal_from_the_companys_quote_email_template(): void
    {
        /*
         * Every company is auto-bootstrapped with a "quote_sent" EmailTemplate
         * (see CompanyDefaultsBootstrapService::bootstrap()), so update it
         * rather than creating a second row with the same title.
         */
        EmailTemplate::forCompany($this->company->id)
            ->where('title', 'quote_sent')
            ->update([
                'subject' => 'New Quote: {{ quote.number }}',
                'body'    => 'Dear {{ customer.name }}, your quote #{{ quote.number }} totals {{ quote.total_formatted }}.',
            ]);

        $quote = $this->createQuote(['quote_total' => 150]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->assertSuccessful()
            ->mountAction('email_quote');

        /* Assert */
        $component
            ->assertActionDataSet([
                'recipient' => 'prospect@example.com',
                'subject'   => 'New Quote: QUO-987654',
                'body'      => "Dear {$quote->prospect->company_name}, your quote #QUO-987654 totals 150.00.",
            ]);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_falls_back_to_a_default_subject_and_blank_body_without_a_template(): void
    {
        /* Arrange */
        EmailTemplate::forCompany($this->company->id)->where('title', 'quote_sent')->delete();

        $quote = $this->createQuote();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->assertSuccessful()
            ->mountAction('email_quote');

        /* Assert */
        $component
            ->assertActionDataSet([
                'recipient' => 'prospect@example.com',
                'subject'   => 'Quote #QUO-987654',
                'body'      => '',
            ]);
    }

    #[Test]
    #[Group('slow')]
    #[Group('crud')]
    public function it_hides_the_action_without_the_email_quotes_permission(): void
    {
        /* Arrange */
        $this->user->syncRoles([]);
        $this->user->givePermissionTo([
            Permission::VIEW_QUOTES->value,
            Permission::EDIT_QUOTES->value,
        ]);
        $quote = $this->createQuote();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('email_quote');
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_queues_the_quote_mailable_logs_a_mail_queue_row_and_marks_the_quote_sent(): void
    {
        /* Arrange */
        Mail::fake();
        $quote = $this->createQuote(['quote_status' => QuoteStatus::DRAFT]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->assertSuccessful()
            ->mountAction('email_quote')
            ->setActionData([
                'recipient' => 'prospect@example.com',
                'subject'   => 'Subject line',
                'body'      => 'Body text',
            ])
            ->callMountedAction();

        /* Assert */
        Mail::assertQueued(QuoteMailable::class, fn ($mail) => $mail->hasTo('prospect@example.com'));

        $this->assertDatabaseHas('mail_queue', [
            'mailable_id'   => $quote->id,
            'mailable_type' => Quote::class,
            'type'          => MailType::SENT->value,
            'to'            => 'prospect@example.com',
        ]);

        $this->assertDatabaseHas('quotes', [
            'id'           => $quote->id,
            'quote_status' => QuoteStatus::SENT->value,
        ]);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_shows_an_error_when_no_contact_email_is_found_on_the_prospect(): void
    {
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->prospect()->create();
        $prospect->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);

        $quote = Quote::factory()->for($this->company)->create([
            'prospect_id'  => $prospect->id,
            'quote_status' => QuoteStatus::DRAFT,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->assertSuccessful();

        /* Assert */
        $component->assertActionDisabled('email_quote');

        $this->assertDatabaseHas('quotes', [
            'id'           => $quote->id,
            'quote_status' => QuoteStatus::DRAFT->value,
        ]);
    }

    private function createQuote(array $attributes = []): Quote
    {
        $prospect = Relation::factory()->for($this->company)->prospect()->create();
        $contact  = $prospect->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'John',
            'last_name'  => 'Prospect',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'prospect@example.com',
        ]);

        /** @var Quote $quote */
        $quote = Quote::factory()->for($this->company)->create(array_merge([
            'quote_number' => 'QUO-987654',
            'prospect_id'  => $prospect->id,
            'user_id'      => $this->user->id,
            'quote_status' => QuoteStatus::SENT,
            'quoted_at'    => '2025-05-10',
            'quote_expires_at' => '2025-06-09',
        ], $attributes));

        return $quote;
    }
}
