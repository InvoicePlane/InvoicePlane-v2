<?php

namespace Modules\Clients\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\CreateQuote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class RelationListRowActionsTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Resource pages gate on Spatie permissions, so the test user
         * needs the seeded client_admin permission set to mount pages.
         */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_create_invoice_action_only_for_customer_relations(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $prospect = Relation::factory()->for($this->company)->prospect()->create();

        /* Act */
        $component = Livewire::actingAs($this->user)->test(ListRelations::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('create_invoice')->table($customer))
            ->assertActionHidden(TestAction::make('create_invoice')->table($prospect));
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_create_quote_action_for_all_relation_types(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $prospect = Relation::factory()->for($this->company)->prospect()->create();

        /* Act */
        $component = Livewire::actingAs($this->user)->test(ListRelations::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('create_quote')->table($customer))
            ->assertActionVisible(TestAction::make('create_quote')->table($prospect));
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_delete_action_when_relation_has_linked_records(): void
    {
        /* Arrange */
        $this->user->givePermissionTo(Permission::DELETE_RELATIONS->value);

        $clean  = Relation::factory()->for($this->company)->customer()->create();
        $billed = Relation::factory()->for($this->company)->customer()->create();

        $documentGroup = Numbering::factory()->for($this->company)->create();
        Invoice::factory()->for($this->company)->create([
            'customer_id'  => $billed->id,
            'numbering_id' => $documentGroup->id,
            'user_id'      => $this->user->id,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)->test(ListRelations::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('delete')->table($clean))
            ->assertActionHidden(TestAction::make('delete')->table($billed));
    }

    #[Test]
    #[Group('crud')]
    public function it_prefills_customer_on_invoice_create_page_from_query_string(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->withQueryParams(['customer_id' => $customer->id])
            ->test(CreateInvoice::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertFormSet(['customer_id' => $customer->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_prefills_prospect_on_quote_create_page_from_query_string(): void
    {
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->prospect()->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->withQueryParams(['customer_id' => $prospect->id])
            ->test(CreateQuote::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertFormSet(['prospect_id' => $prospect->id]);
    }
}
