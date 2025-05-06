<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\CreateContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\EditContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ContactsTest extends AbstractTestCase
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
     * @payload ['relation_id' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe', 'gender' => 'female']
     */
    public function it_lists_contacts(): void
    {
        $relation = Relation::factory()->for($this->user->company)->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        $contact = Contact::factory()->for($this->user->company)->create($payload);

        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($contact);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload ['relation_id' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe', 'gender' => 'female']
     */
    public function it_creates_a_contact(): void
    {
        $relation = Relation::factory()->for($this->user->company)->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        Livewire::test(CreateContact::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['first_name' => 'Jane']
     */
    public function it_fails_when_relation_id_is_missing(): void
    {
        $payload = [
            // 'relation_id' => 1,
            'first_name' => 'Jane',
        ];

        Livewire::test(CreateContact::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['relation_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['relation_id' => 1]
     */
    public function it_fails_when_first_name_is_missing(): void
    {
        $relation = Relation::factory()->for($this->user->company)->create();

        $payload = [
            'relation_id' => $relation->id,
            // 'first_name' => 'Jane',
        ];

        Livewire::test(CreateContact::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['first_name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['first_name' => 'Updated', 'last_name' => 'Contact']
     */
    public function it_updates_a_contact(): void
    {
        $payload = [
            'first_name' => 'Initial',
            'last_name'  => 'Contact',
            'gender'     => 'male',
        ];

        $contact = Contact::factory()->for($this->user->company)->create($payload);

        $update = [
            'first_name' => 'Updated',
            'last_name'  => 'Contact',
        ];

        Livewire::test(EditContact::class, ['record' => $contact->getKey()])
            ->actingAs($this->user)
            ->fillForm($update)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $update);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_contact(): void
    {
        $contact = Contact::factory()->for($this->user->company)->create();

        Livewire::test(ListContacts::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $contact);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    # endregion

    #region spicy
    # endregion
}
