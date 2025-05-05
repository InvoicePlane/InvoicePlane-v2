<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\CustomerResource;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\CreateCustomer;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\ListCustomers;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\CustomerAssignmentService;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CustomerResource::class)]
class CustomersTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload
     * {
     * "relation_id": 51,
     * "first_name": "John",
     * "last_name": "Doe",
     * "gender": "male"
     * }
     */
    public function it_lists_customers(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'company_id'         => $company->id,
            'primary_contact_id' => null,
            'relation_type'      => RelationType::CUSTOMER,
            'relation_status'    => 'active',
            'relation_number'    => '::relation_number::',
            'company_name'       => 'Acme Corp',
            'trading_name'       => '::trading_name::',
            'id_number'          => 'ID123456',
            'coc_number'         => 'COC789',
            'vat_number'         => 'VAT999',
            'registered_at'      => now()->format('Y-m-d'),
        ];

        Relation::query()->create($payload);

        Livewire::test(ListCustomers::class)
            ->assertSuccessful()
            ->assertSee('Acme Corp');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "primary_contact_id": null,
     *   "relation_type": "customer",
     *   "relation_status": "active",
     *   "relation_number": "CUST-ABC123",
     *   "company_name": "::company_name::",
     *   "trading_name": "::trading_name::",
     *   "id_number": "ID123456",
     *   "coc_number": "COC789",
     *   "vat_number": "VAT999",
     *   "registered_at": "2025-05-04"
     * }
     */
    public function it_creates_a_customer(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'company_id'         => $company->id,
            'primary_contact_id' => null,
            'relation_type'      => RelationType::CUSTOMER,
            'relation_status'    => 'active',
            'relation_number'    => '::relation_number::',
            'company_name'       => '::company_name::',
            'trading_name'       => '::trading_name::',
            'id_number'          => 'ID123456',
            'coc_number'         => 'COC789',
            'vat_number'         => 'VAT999',
            'registered_at'      => now()->format('Y-m-d'),
        ];

        Livewire::test(CreateCustomer::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "primary_contact_id": 2,
     *   "relation_type": "customer",
     *   "relation_status": "active",
     *   "company_name": "::company_name::",
     *   "trading_name": "::trading_name::",
     *   "id_number": "ID123456",
     *   "coc_number": "COC789",
     *   "vat_number": "VAT999",
     *   "registered_at": "2025-05-04"
     * }
     */
    public function it_fails_to_create_customer_when_relation_number_missing(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'company_id'         => $company->id,
            'primary_contact_id' => null,
            'relation_type'      => RelationType::CUSTOMER,
            'relation_status'    => 'active',
            'company_name'       => '::company_name::',
            'trading_name'       => '::trading_name::',
            'id_number'          => 'ID123456',
            'coc_number'         => 'COC789',
            'vat_number'         => 'VAT999',
            'registered_at'      => now()->format('Y-m-d'),
        ];

        Livewire::test(CreateCustomer::class)
            ->fillForm(['data' => $payload])
            ->call('create')
            ->assertHasFormErrors(['relation_number' => 'required']);

        $this->assertDatabaseCount('relations', 0);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "primary_contact_id": 2,
     *   "relation_type": "customer",
     *   "relation_status": "active",
     *   "relation_number": "CUST-ABC123",
     *   "trading_name": "::trading_name::",
     *   "id_number": "ID123456",
     *   "coc_number": "COC789",
     *   "vat_number": "VAT999",
     *   "registered_at": "2025-05-04"
     * }
     */
    public function it_fails_to_create_customer_when_company_name_missing(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'company_id'         => $company->id,
            'primary_contact_id' => null,
            'relation_type'      => RelationType::CUSTOMER,
            'relation_status'    => 'active',
            'relation_number'    => 'CUST-' . Str::random(6),
            'trading_name'       => '::trading_name::',
            'id_number'          => 'ID123456',
            'coc_number'         => 'COC789',
            'vat_number'         => 'VAT999',
            'registered_at'      => now()->format('Y-m-d'),
        ];

        Livewire::test(CreateCustomer::class)
            ->fillForm(['data' => $payload])
            ->call('create')
            ->assertHasFormErrors(['company_name' => 'required']);

        $this->assertDatabaseCount('relations', 0);
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Clients\Filament\Company\Resources\CustomerResource.
     *
     * @payload
     * []
     */
    public function it_updates_a_customer(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Relation::factory()->create();

        $payload = [
        ];

        Livewire::test(EditRelation::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Clients\Filament\Company\Resources\CustomerResource.
     *
     * @payload
     * []
     */
    public function it_deletes_a_customer(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Relation::factory()->create();

        Livewire::test(ListCustomers::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('customers', ['id' => $record->id]);
    }
    // endregion

    // region usp
    /**
     * @payload ["userId" => $user->id, "customerId" => $customer->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_assigns_user_to_customer_successfully(): void
    {
        $this->markTestIncomplete();

        $user     = User::factory()->create();
        $customer = Relation::factory()->create();
        $service  = new CustomerAssignmentService();
        $result   = $service->assign($user->id, $customer->id);
        if (app()->isLocal()) {
            dump($result);
        }
        $this->assertTrue($result);
        $this->assertDatabaseHas('customer_user', [
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * @payload ["customerId" => $customer->id, "note" => "Test note"]
     */
    #[Test]
    #[Group('spicy')]
    public function it_adds_note_to_customer_successfully(): void
    {
        $this->markTestIncomplete();

        $customer = Relation::factory()->create();
        $service  = new NoteService();
        $note     = $service->addNote($customer->id, 'Test note');
        if (app()->isLocal()) {
            dump($note);
        }
        $this->assertDatabaseHas('customer_notes', [
            'customer_id' => $customer->id,
            'note'        => 'Test note',
        ]);
    }
    // endregion
}
