<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\CustomerResource;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\CreateCustomer;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\EditCustomer;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\ListCustomers;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CustomerResource::class)]
class CustomersTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['company_name' => 'Acme Inc.', 'relation_type' => 'customer']
     */
    public function it_lists_customers(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [
            'company_name'  => 'Acme Inc.',
            'relation_type' => RelationType::CUSTOMER,
        ];

        $customer = Relation::factory()->for($this->user->company)->create($payload);

        Livewire::test(ListCustomers::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($customer);
    }
    #endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload ['company_name' => 'Beta LLC', 'relation_type' => 'customer', 'relation_number' => 'C123']
     */
    public function it_creates_a_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [
            'company_name'    => 'Beta LLC',
            'relation_type'   => RelationType::CUSTOMER,
            'relation_number' => 'C123',
        ];

        Livewire::test(CreateCustomer::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('relations', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['relation_type' => 'customer']
     */
    public function it_fails_when_company_name_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [
            // 'company_name' => 'Missing Inc.',
            'relation_type' => RelationType::CUSTOMER,
        ];

        Livewire::test(CreateCustomer::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['company_name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['company_name' => 'Zeta Ltd.']
     */
    public function it_fails_when_relation_type_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [
            'company_name' => 'Zeta Ltd.',
            // 'relation_type' => RelationType::CUSTOMER,
        ];

        Livewire::test(CreateCustomer::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['relation_type']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['company_name' => 'Updated Name']
     */
    public function it_updates_a_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $original = [
            'company_name'  => 'Old Name',
            'relation_type' => RelationType::CUSTOMER,
        ];

        $customer = Relation::factory()->for($this->user->company)->create($original);

        $update = [
            'company_name' => 'Updated Name',
        ];

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->actingAs($this->user)
            ->fillForm($update)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('relations', $update);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['company_name' => null]
     */
    public function it_fails_to_update_if_company_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $customer = Relation::factory()->for($this->user->company)->create([
            'company_name'  => 'Will Not Update',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        $payload = [
            // 'company_name' => 'Blank',
        ];

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['company_name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $customer = Relation::factory()->for($this->user->company)->create([
            'company_name'  => 'Delete Me',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        Livewire::test(ListCustomers::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $customer);

        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_customer_when_not_owner(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $otherUser = User::factory()->withCompany()->create();
        $customer  = Relation::factory()->for($otherUser->company)->create([
            'company_name'  => 'Other Corp',
            'relation_type' => RelationType::CUSTOMER,
        ]);

        Livewire::test(ListCustomers::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $customer)
            ->assertHasErrors();

        $this->assertDatabaseHas('relations', ['id' => $customer->id]);
    }
    # endregion

    # region spicy
    # endregion
}
