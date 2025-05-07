<?php

namespace Modules\Clients\Services;

use Modules\Clients\Models\Contact;

use Modules\Clients\Services\CustomerAssignmentService;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Models\Relation;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CustomerAssignmentServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_assigns_contact_to_customer(): void
    {
        $customer = Relation::factory()->create();
        $contact  = Contact::factory()->create();

        CustomerAssignmentService::assign($contact, $customer);

        $this->assertEquals($customer->id, $contact->customer_id);
    }

    #[Test]
    #[Group('services')]
    public function it_does_nothing_if_already_assigned(): void
    {
        $customer = Relation::factory()->create();
        $contact  = Contact::factory()->for($customer)->create();
        CustomerAssignmentService::assign($contact, $customer);

        $this->assertEquals($customer->id, $contact->customer_id);
    }
}
