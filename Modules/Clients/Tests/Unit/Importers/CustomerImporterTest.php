<?php

namespace Modules\Clients\Tests\Unit\Importers;

use Modules\Clients\Importers\CustomerImporter;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('support')]
class CustomerImporterTest extends AbstractTestCase
{
    #[Test]
    public function it_imports_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $importer = new CustomerImporter();
        $result   = $importer->run(['name' => 'Example']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('customers', ['name' => 'Example']);
    }
}
