<?php

namespace Modules\Clients\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\ListCustomers;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CustomersTest extends AbstractTestCase
{
    #[Test]
    #[Group('testing')]
    public function it_lists_customers(): void
    {
        $this->markTestSkipped();

        /* arrange */
        $payload = [
            'company_name'  => 'Acme Inc.',
            'relation_type' => RelationType::CUSTOMER,
        ];

        $customer = Relation::factory()->for($this->user->company)->create($payload);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListCustomers::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($customer);
    }
}
