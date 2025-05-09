<?php

namespace Modules\Clients\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\CreateContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\EditContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

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
            ->for($this->user->companies()->first(), 'company')
            ->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        Contact::factory()->for($this->user->companies()->first())->create($payload);

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

        /** act */
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
        $this->markTestIncomplete();

        /* arrange */

        $payload = [
            // 'relation_id' => 1,
            'first_name' => 'Jane',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateContact::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['relation_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_when_first_name_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $relation = Relation::factory()->for($this->user->companies()->first())->create();

        $payload = [
            'relation_id' => $relation->id,
            // 'first_name' => 'Jane',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateContact::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['first_name']);
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

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditContact::class, ['record' => $contact->getKey()])->fillForm($update)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $update);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_contact(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $contact = Contact::factory()->for($this->user->companies()->first())->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListContacts::class)->callTableAction('delete', $contact);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    # endregion

    #region spicy
    # endregion
}
