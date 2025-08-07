<?php

namespace Modules\Clients\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\CreateContact;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\EditContact;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListContacts::class)]
class ContactsTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    #region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['relation_id' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe', 'gender' => 'female']
     */
    public function it_lists_contacts(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        Contact::factory()->for($this->company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertSee('Jane Doe');
        $this->assertDatabaseHas('contacts', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "relation_id": "<relation_id>",
     *   "first_name": "Jane",
     *   "last_name": "Doe",
     *   "gender": "female"
     * }
     */
    public function it_creates_a_contact_trough_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoActionErrors();

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('contacts', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "first_name": "Jane",
     *   "last_name": "Doe",
     *   "gender": "female"
     * }
     */
    public function it_fails_trough_a_modal_when_relation_id_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payload = [
            //'relation_id' => $relation->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'gender'     => 'female',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['relation_id' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "relation_id": "<relation_id>",
     *   "last_name": "Doe",
     *   "gender": "female"
     * }
     */
    public function it_fails_trough_a_modal_when_first_name_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $relation = Relation::factory()
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['first_name' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "relation_id": "<relation_id>",
     *   "first_name": "Jane",
     *   "gender": "female"
     * }
     */
    public function it_fails_trough_a_modal_when_last_name_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $relation = Relation::factory()
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'gender'      => 'female',
        ];

        /* act & assert */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['last_name' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "first_name": "Updated",
     *   "last_name": "Contact"
     * }
     */
    public function it_updates_a_contact_trough_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payload = [
            'first_name' => 'Initial',
            'last_name'  => 'Contact',
            'gender'     => 'male',
        ];

        $contact = Contact::factory()->for($this->user->companies()->first())->create($payload);

        $update = [
            'first_name' => 'Updated',
            'last_name'  => 'Contact',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('edit', ['record' => $contact->getKey()])
            ->fillForm($update)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contacts', $update);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_contact(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateContact::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_relation_id_is_missing(): void
    {
        /* arrange */
        $payload = [
            //'relation_id' => $relation->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'gender'     => 'female',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateContact::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_first_name_is_missing(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateContact::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['first_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_last_name_is_missing(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'gender'      => 'female',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateContact::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['last_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_contact(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payload = [
            'first_name' => 'Initial',
            'last_name'  => 'Contact',
            'gender'     => 'male',
        ];

        $contact = Contact::factory()->for($this->user->companies()->first())->create($payload);

        $update = [
            'first_name' => 'Updated',
            'last_name'  => 'Contact',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditContact::class, ['record' => $contact->getKey()])->fillForm($update)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contacts', $update);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_contact(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $contact = Contact::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->callAction('delete', $contact);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_contacts_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s contact.');

        /* arrange */
        // Create two different companies
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->for($this->user)->create();

        // Create a user in company1
        $user1 = User::factory()->create();
        $user1->companies()->attach($company1);

        // Create a user in company2
        $user2 = User::factory()->create();
        $user2->companies()->attach($company2);

        // Create a contact for user2's company
        $contact = Contact::factory()->for($company2)->create();

        /* act */
        // Try to access the contact as user1 (different company)
        $response = $this->actingAs($user1)
            ->get(route('filament.company.resources.contacts.index'));

        /* assert */
        // Verify access is denied (403 Forbidden or 404 Not Found)
        $response->assertStatus(403); // or 404, depending on your implementation
    }
    # endregion

    #region spicy
    # endregion
}
