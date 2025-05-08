<?php

namespace Modules\Clients\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\CustomerService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CustomerServiceTest extends AbstractTestCase
{
    #[Test]
    public function it_creates_a_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $service = new CustomerService();

        $client = $service->create([
            'name'  => 'Example Co.',
            'email' => 'info@example.com',
        ]);

        $this->assertInstanceOf(Relation::class, $client);
        $this->assertEquals('Example Co.', $client->name);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_without_email(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(InvalidArgumentException::class);

        (new CustomerService())->create([
            'name' => 'No Email Co.',
        ]);
    }
}
