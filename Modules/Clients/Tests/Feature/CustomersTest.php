<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\CreateRelation;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\EditRelation;
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
    public function it_creates_a_customer_trough_a_modal(): void
    {
        $this->markTestIncomplete();

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
            ->assertHasNoActionErrors();

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
    public function it_fails_trough_a_modal_without_required_company_name(): void
    {
        $this->markTestIncomplete();

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
    public function it_fails_trough_a_modal_without_required_relation_type(): void
    {
        $this->markTestIncomplete();

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
    public function it_fails_trough_a_modal_without_required_relation_status(): void
    {
        $this->markTestIncomplete();

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
    public function it_fails_trough_a_modal_without_required_registered_at(): void
    {
        $this->markTestIncomplete();

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
    public function it_updates_a_customer_trough_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $original = [
            'company_name'  => 'Old Name',
            'relation_type' => RelationType::CUSTOMER,
        ];

        $customer = Relation::factory()->for($this->user->companies()->first())->create($original);

        $update = [
            'company_name' => 'Updated Name',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('edit', ['record' => $customer->getKey()])
            ->fillForm($update)
            ->callMountedAction()
            ->assertHasNoActionErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('relations', array_merge(['id' => $customer->id], $update));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_trough_a_modal_without_required_company_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $customer = Relation::factory()->for($this->user->companies()->first())->create([
            'company_name'  => 'Will Not Update',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        $payload = [
            'company_name' => '',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('edit', ['record' => $customer->getKey()])
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['company_name' => 'required']);
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
    public function it_fails_when_company_name_is_missing(): void
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
    public function it_fails_when_relation_type_is_missing(): void
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
        $component = Livewire::actingAs($this->user)->test(CreateRelation::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_type']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_relation_status_is_missing(): void
    {
        /* arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(CreateRelation::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_status']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_registered_at_is_missing(): void
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
    public function it_updates_a_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $original = [
            'company_name'  => 'Old Name',
            'relation_type' => RelationType::CUSTOMER,
        ];

        $customer = Relation::factory()->for($this->user->companies()->first())->create($original);

        $update = [
            'company_name' => 'Updated Name',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditRelation::class, ['record' => $customer->getKey()])->fillForm($update)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('relations', $update);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_if_company_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $customer = Relation::factory()->for($this->user->companies()->first())->create([
            'company_name'  => 'Will Not Update',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        $payload = [
            // 'company_name' => 'Blank',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditRelation::class, ['record' => $customer->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['company_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_customer(): void
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
            ->callAction('delete', $customer);

        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_customer_when_not_owner(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $otherUser = User::factory()->withCompany()->create();
        $customer  = Relation::factory()->for($otherUser->company)->create([
            'company_name'  => 'Other Corp',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('delete', $customer);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseHas('relations', ['id' => $customer->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_customers_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s customer.');
    }
    # endregion

    # region spicy
    # endregion
}
