<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\ContactResource;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\CreateContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\EditContact;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ContactResource::class)]

class ContactsTest extends AbstractTestCase
{
    use RefreshDatabase;
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
    public function it_lists_contacts(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        $relation = Relation::factory()->create();

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'gender'      => 'male',
        ];

        $contact = Contact::query()->create($payload);

        Livewire::test(ListContacts::class)
            ->assertSuccessful()
            ->assertSee($contact->first_name);
    }
    // endregion

    // region crud
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
    public function it_creates_a_contact(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        $relation = Relation::factory()->create();

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'relation_id' => $relation->id,
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'gender'      => 'male',
        ];

        Livewire::test(CreateContact::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('smoke')]
    /**
     * \Modules\Clients\Filament\Company\Resources\ContactResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "relation_id": "Value",
     * "first_name": "Example",
     * "last_name": "Example",
     * "gender": "Value"
     * }
     */
    public function it_fails_to_creates_contact_when_relation_not_filled(): void
    {
        // Create a company and associate it with the current user
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'relation_id' => 1,
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'gender'      => 'male',
        ];

        Livewire::test(CreateContact::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['data.relation_id']);
        if (app()->isLocal() || app()->runningUnitTests()) {
            $errors      = Livewire::test(CreateContact::class)->errors();
            $failedRules = Livewire::test(CreateContact::class)->failedRules();
        }
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Clients\Filament\Company\Resources\ContactResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "relation_id": "Value",
     * "first_name": "Example",
     * "last_name": "Example",
     * "gender": "Value"
     * }
     */
    public function it_updates_a_contact(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Contact::factory()->create();

        $payload = [
            'company_id'  => 'Value',
            'relation_id' => 'Value',
            'first_name'  => 'Example',
            'last_name'   => 'Example',
            'gender'      => 'Value',
        ];

        Livewire::test(EditContact::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Clients\Filament\Company\Resources\ContactResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "relation_id": "Value",
     * "first_name": "Example",
     * "last_name": "Example",
     * "gender": "Value"
     * }
     */
    public function it_deletes_a_contact(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Contact::factory()->create();

        Livewire::test(ListContacts::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('contacts', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
