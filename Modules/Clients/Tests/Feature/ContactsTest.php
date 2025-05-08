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
        $this->markTestIncomplete();
        /* arrange */

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
        $this->markTestIncomplete();

        /* arrange */

        $relation = Relation::factory()->for($this->user->company)->create();

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateContact::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['first_name' => 'Jane']
     */
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
    /**
     * @payload ['relation_id' => 1]
     */
    public function it_fails_when_first_name_is_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $relation = Relation::factory()->for($this->user->company)->create();

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
    /**
     * @payload ['first_name' => 'Updated', 'last_name' => 'Contact']
     */
    public function it_updates_a_contact(): void
    {
        $this->markTestIncomplete();

        /* arrange */

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditContact::class, ['record' => $contact->getKey()])->fillForm($update)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', $update);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_contact(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $contact = Contact::factory()->for($this->user->company)->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListContacts::class)->callTableAction('delete', $contact);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    # endregion

    #region spicy
    # endregion
}
