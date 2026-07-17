<?php

namespace Modules\Clients\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\CommunicationType;
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
    #[Group('failing')]
    public function it_deletes_a_customer(): void
    {
        $this->markTestSkipped('FK constraint on contacts table prevents Filament delete action — needs cascade or contact cleanup first');
    }

    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_fails_to_delete_customer_when_contact_attached(): void
    {
        $this->markTestSkipped('Preventing deletion of customers with attached contacts is not yet implemented');
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_returns_customers_belonging_to_the_current_tenant(): void
    {
        /* Arrange */
        $companyB  = \Modules\Core\Models\Company::factory()->create();
        $customerA = Relation::factory()->for($this->company)->customer()->create();
        $customerB = Relation::factory()->for($companyB)->customer()->create();

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

        $customerA = Relation::factory()->for($this->company)->customer()->create(['company_name' => 'VISIBLE']);
        $customerB = Relation::factory()->for($companyB)->customer()->create(['company_name' => 'HIDDEN']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSeeText('VISIBLE');
        $this->assertDatabaseHas('relations', ['id' => $customerB->id]);
        $component->assertDontSeeText('HIDDEN');
    }
    # endregion

    # region cc emails
    #[Test]
    #[Group('crud')]
    public function it_stores_and_retrieves_cc_emails_as_communications(): void
    {
        /* Arrange */
        $cc = ['billing@acme.com', 'finance@acme.com'];

        /* Act */
        $customer = Relation::factory()->for($this->company)->create();
        foreach ($cc as $email) {
            $customer->communications()->create([
                'company_id'          => $this->company->id,
                'communication_type'  => CommunicationType::INVOICE_CC->value,
                'communication_value' => $email,
                'is_primary'          => false,
            ]);
        }
        $loaded = Relation::find($customer->id);

        /* Assert */
        $this->assertEqualsCanonicalizing($cc, $loaded->email_cc);
        foreach ($cc as $email) {
            $this->assertDatabaseHas('communications', [
                'communicationable_id'   => $customer->id,
                'communicationable_type' => Relation::class,
                'communication_type'     => CommunicationType::INVOICE_CC->value,
                'communication_value'    => $email,
            ]);
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_customer_with_cc_emails_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
            'email_cc'        => ['billing@acme.com', 'finance@acme.com'],
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

        $customer = Relation::where('company_name', $payload['company_name'])->firstOrFail();

        foreach ($payload['email_cc'] as $email) {
            $this->assertDatabaseHas('communications', [
                'communicationable_id'   => $customer->id,
                'communicationable_type' => Relation::class,
                'communication_type'     => CommunicationType::INVOICE_CC->value,
                'communication_value'    => $email,
            ]);
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_through_a_modal_with_an_invalid_cc_email(): void
    {
        /* Arrange */
        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_status' => RelationStatus::ACTIVE,
            'relation_number' => 'C123',
            'registered_at'   => Carbon::parse('2025-01-01')->toDateString(),
            'email_cc'        => ['not-an-email'],
        ];

        /* Act & Assert */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['email_cc.0' => 'email']);
    }
    # endregion

    # region spicy
    # endregion
}
