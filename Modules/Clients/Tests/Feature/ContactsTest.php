<?php

namespace Modules\Clients\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\CreateContact;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListContacts::class)]
class ContactsTest extends AbstractCompanyPanelTestCase
{
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
    public function it_creates_a_contact_through_a_modal(): void
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

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
    public function it_fails_through_a_modal_without_required_relation_id(): void
    {
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
    public function it_fails_through_a_modal_without_required_first_name(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
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
    public function it_fails_through_a_modal_without_required_last_name(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
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
    public function it_updates_a_contact_through_a_modal(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
            ->create();

        $payload = [
            'first_name' => 'Initial',
            'last_name'  => 'Contact',
            'gender'     => Gender::MALE,
        ];

        $contact = Contact::factory()
            ->for($this->company)
            ->for($relation)
            ->create($payload);

        $updatedData = [
            'first_name' => 'Updated',
            'last_name'  => 'Contact',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction(TestAction::make('edit')->table($contact), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contacts', $updatedData);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_contact(): void
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
    public function it_fails_to_create_without_required_relation_id(): void
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
    public function it_fails_to_create_without_required_first_name(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
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
    public function it_fails_to_create_without_required_last_name(): void
    {
        /* arrange */
        $relation = Relation::factory()
            ->for($this->company, 'company')
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
    public function it_deletes_a_contact(): void
    {
        /* arrange */
        $relation = Relation::factory()->for($this->company, 'company')->create();
        $contact  = Contact::factory()->for($this->company)->create([
            'relation_id' => $relation->id,
            'first_name'  => 'DeleteMe',
            'last_name'   => 'Contact',
            'gender'      => 'female',
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction(TestAction::make('delete')->table($contact))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
