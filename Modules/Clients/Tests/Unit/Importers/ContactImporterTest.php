<?php

namespace Modules\Clients\Tests\Unit\Importers;

use InvalidArgumentException;
use Modules\Clients\Importers\ContactImporter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('support')]
class ContactImporterTest extends TestCase
{
    #[Test]
    public function it_creates_contact(): void
    {
        $importer = new ContactImporter();
        $result   = $importer->run(['first_name' => 'Jane']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('contacts', ['first_name' => 'Jane']);
    }

    #[Test]
    public function it_requires_first_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ContactImporter())->run([]);
    }
}
