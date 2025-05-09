<?php

namespace Modules\Clients\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ContactsTest extends AbstractTestCase
{
    #[Test]
    #[Group('testing')]
    public function it_lists_contacts(): void
    {
        $this->markTestIncomplete();
        $this->assertTrue(true);

        /* arrange */
        $user     = new User();
        $relation = Relation::factory()->for($this->user->company)->create();

        $payload = ['relation_id' => $relation->id,
            'first_name'          => 'Jane',
            'last_name'           => 'Doe',
            'gender'              => 'female',
        ];

        $contact = Contact::factory()->for($this->user->company)->create($payload);

        Livewire::actingAs($user)
            ->test(ListContacts::class)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($contact);
    }
}
