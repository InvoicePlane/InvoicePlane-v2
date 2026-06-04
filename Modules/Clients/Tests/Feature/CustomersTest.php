<?php

namespace Modules\Clients\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\CreateRelation;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListRelations::class)]
class CustomersTest extends AbstractCompanyPanelTestCase
{
    #region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['company_name' => 'Acme Inc.', 'relation_type' => 'customer']
     */
    #[Group('crud')]
    public function it_lists_customers(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        $customer = Relation::factory()->for($this->company)->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('relations', $payload);
    }
    #endregion

    # region modals
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_name": "Beta LLC",
     *   "relation_type": "CUSTOMER",
     *   "relation_status": "ACTIVE",
     *   "relation_number": "C123",
     *   "registered_at": "2025-01-01"
     * }
     */
    public function it_creates_a_customer_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('relations', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "relation_type": "CUSTOMER",
     *   "relation_status": "ACTIVE",
     *   "relation_number": "C123"
     * }
     */
    public function it_fails_through_a_modal_without_required_company_name(): void
    {
        /* Arrange */
        $payload = [
            // 'company_name' => 'Missing Inc.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['company_name' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_name": "Zeta Ltd.",
     *   "relation_status": "ACTIVE",
     *   "relation_number": "C123",
     *   "registered_at": "2025-01-01"
     * }
     */
    public function it_fails_through_a_modal_without_required_relation_type(): void
    {
        /* Arrange */
        $payload = [
            'company_name' => 'Zeta Ltd.',
            // 'relation_type' => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['relation_type' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_name": "Beta LLC",
     *   "relation_type": "CUSTOMER",
     *   "relation_number": "C123",
     *   "registered_at": "2025-01-01"
     * }
     */
    public function it_fails_through_a_modal_without_required_relation_status(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['relation_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_name": "Zeta Ltd.",
     *   "relation_type": "CUSTOMER",
     *   "relation_number": "C123"
     * }
     */
    public function it_fails_through_a_modal_without_required_registered_at(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Zeta Ltd.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['registered_at' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_name": "Updated Name"
     * }
     */
    public function it_updates_a_customer_through_a_modal(): void
    {
        /* Arrange */
        $original = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        $customer = Relation::factory()
            ->for($this->company)
            ->create($original);

        $updatedData = [
            'company_name' => 'InvoicePlane LLC Limited',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction(TestAction::make('edit')->table($customer), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component
            ->assertSuccessful();

        $this->assertDatabaseHas(
            'relations',
            array_merge(
                [
                    'id' => $customer->id,
                ],
                $updatedData
            )
        );
    }
    #endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_customer(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('relations', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_company_name(): void
    {
        /* Arrange */
        $payload = [
            // 'company_name' => 'Missing Inc.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['company_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_relation_type(): void
    {
        /* Arrange */
        $payload = [
            'company_name' => 'Zeta Ltd.',
            // 'relation_type' => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['relation_type']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_relation_status(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['relation_status']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_registered_at(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Zeta Ltd.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['registered_at']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_customer(): void
    {
        $this->markTestIncomplete('foreign key contact');

        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create([
            'company_name'  => 'Delete Me',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction(TestAction::make('delete')->table($customer))
            ->callMountedAction();

        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_customer_when_contact_attached(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create([
            'company_name'  => 'Delete Me',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction(TestAction::make('delete')->table($customer))
            ->callMountedAction();

        /* Assert */
        $component
            ->assertHasErrors();

        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_returns_customers_belonging_to_the_current_tenant(): void
    {
        /* Arrange */
        $companyB   = \Modules\Core\Models\Company::factory()->create();
        $customerA  = Relation::factory()->for($this->company)->customer()->create();
        $customerB  = Relation::factory()->for($companyB)->customer()->create();

        /* Act — authenticate as Company A user; global scope filters to Company A */
        $this->actingAs($this->user);

        /* Assert */
        $this->assertDatabaseHas('relations', ['id' => $customerA->id]);
        $this->assertDatabaseHas('relations', ['id' => $customerB->id]);    // B is in the DB...
        $this->assertNotNull(Relation::find($customerA->id));               // A is visible to tenant A
        $this->assertNull(Relation::find($customerB->id));                  // B is NOT visible to tenant A
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_lists_customers_for_the_current_tenant(): void
    {
        /* Arrange */
        $companyB = \Modules\Core\Models\Company::factory()->create();

        $customerA = Relation::factory()->for($this->company)->customer()->create(['company_name' => 'Visible Customer']);
        $customerB = Relation::factory()->for($companyB)->customer()->create(['company_name' => 'Hidden Customer']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('relations', ['id' => $customerA->id]);
        $this->assertDatabaseHas('relations', ['id' => $customerB->id]);
        $component->assertSeeText('Visible Customer');
        $component->assertDontSeeText('Hidden Customer');
    }
    # endregion

    # region spicy
    # endregion
}
