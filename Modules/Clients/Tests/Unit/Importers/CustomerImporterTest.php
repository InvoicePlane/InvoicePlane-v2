<?php

namespace Modules\Clients\Tests\Unit\Importers;

use Modules\Clients\Tests\Unit\Importers\CustomerImporterTest;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Clients\Importers\CustomerImporter;

use Modules\Core\Support\Results\Clients;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

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
