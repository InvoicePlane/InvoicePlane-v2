<?php

namespace Modules\Clients\Tests\Unit\Services;

use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CustomerAssignmentServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_assigns_contact_to_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $customer = Relation::factory()->create();
        $contact  = Contact::factory()->create();

        CustomerAssignmentService::assign($contact, $customer);

        $this->assertEquals($customer->id, $contact->customer_id);
    }

    #[Test]
    #[Group('services')]
    public function it_does_nothing_if_already_assigned(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $customer = Relation::factory()->create();
        $contact  = Contact::factory()->for($customer)->create();
        CustomerAssignmentService::assign($contact, $customer);

        $this->assertEquals($customer->id, $contact->customer_id);
    }
}
