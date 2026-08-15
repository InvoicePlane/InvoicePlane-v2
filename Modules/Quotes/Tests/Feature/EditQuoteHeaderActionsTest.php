<?php

namespace Modules\Quotes\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\EditQuote;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditQuote::class)]
#[Group('slow')]
class EditQuoteHeaderActionsTest extends AbstractCompanyPanelTestCase
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
    public function it_shows_all_header_actions_on_draft_quote(): void
    {
        /* Arrange */
        $quote = $this->createQuote(QuoteStatus::DRAFT);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible('download_pdf')
            ->assertActionVisible('send_email')
            ->assertActionVisible('convert_to_invoice')
            ->assertActionVisible('copy_quote')
            ->assertActionVisible('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_delete_on_approved_quote(): void
    {
        /* Arrange */
        $quote = $this->createQuote(QuoteStatus::APPROVED);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_delete_on_rejected_quote(): void
    {
        /* Arrange */
        $quote = $this->createQuote(QuoteStatus::REJECTED);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_copies_quote_as_new_draft(): void
    {
        /* Arrange */
        $quote = $this->createQuote(QuoteStatus::APPROVED);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->callAction('copy_quote');

        /* Assert */
        $component->assertSuccessful();

        $copy = Quote::query()
            ->whereKeyNot($quote->id)
            ->where('prospect_id', $quote->prospect_id)
            ->firstOrFail();

        $this->assertSame(QuoteStatus::DRAFT, $copy->quote_status);
        $this->assertNull($copy->quote_number);
    }

    private function createQuote(QuoteStatus $status, array $attributes = []): Quote
    {
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        /** @var Quote $quote */
        $quote = Quote::factory()->for($this->company)->create(array_merge([
            'quote_number' => 'Q-987654',
            'prospect_id'  => $prospect->getKey(),
            'numbering_id' => $documentGroup->getKey(),
            'user_id'      => $this->user->id,
            'quote_status' => $status->value,
            'quoted_at'    => '2025-05-10',
        ], $attributes));

        return $quote;
    }
}
