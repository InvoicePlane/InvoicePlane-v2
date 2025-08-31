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
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListRelations::class)]
class CustomersTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    #region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['company_name' => 'Acme Inc.', 'relation_type' => 'customer']
     */
    #[Group('crud')]
    public function it_lists_customers(): void
    {
        /* arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        $customer = Relation::factory()->for($this->user->companies()->first())->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        /* assert */
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
        /* arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
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
        /* arrange */
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
        /* arrange */
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
        /* arrange */
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
        /* arrange */
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
        /* arrange */
        $original = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        $customer = Relation::factory()
            ->for($this->user->companies()->first())
            ->create($original);

        $updatedData = [
            'company_name' => 'InvoicePlane LLC Limited',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction(TestAction::make('edit')->table($customer), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
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
        /* arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('relations', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_company_name(): void
    {
        /* arrange */
        $payload = [
            // 'company_name' => 'Missing Inc.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['company_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_relation_type(): void
    {
        /* arrange */
        $payload = [
            'company_name' => 'Zeta Ltd.',
            // 'relation_type' => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_type']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_relation_status(): void
    {
        /* arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_status']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_registered_at(): void
    {
        /* arrange */
        $payload = [
            'company_name'    => 'Zeta Ltd.',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRelation::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['registered_at']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_customer(): void
    {
        $this->markTestIncomplete('foreign key contact');

        /* arrange */
        $customer = Relation::factory()->for($this->user->companies()->first())->create([
            'company_name'  => 'Delete Me',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        /* act */
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

        /* arrange */
        $customer = Relation::factory()->for($this->user->companies()->first())->create([
            'company_name'  => 'Delete Me',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction(TestAction::make('delete')->table($customer))
            ->callMountedAction();

        /* assert */
        $component
            ->assertHasErrors();

        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion
}
