<?php

namespace Modules\Clients\Tests\Unit\Importers;

use Modules\Clients\Importers\CustomerImporter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Modules\Core\Tests\AbstractTestCase;

#[Group('support')]
class CustomerImporterTest extends TestCase
{
    #[Test]
    public function it_imports_customer(): void
    {
        $importer = new CustomerImporter();
        $result   = $importer->run(['name' => 'Example']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('customers', ['name' => 'Example']);
    }
}
