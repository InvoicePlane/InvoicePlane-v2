<?php

namespace Modules\Invoices\Tests\Unit\Importers;

use InvalidArgumentException;
use Modules\Core\Importers\InvoiceImporter;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceImporterTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_imports_invoice(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $importer = new InvoiceImporter();
        $result   = $importer->run(['number' => 'INV-123', 'customer_id' => 1]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('invoices', ['number' => 'INV-123']);
    }

    #[Test]
    #[Group('support')]
    public function it_fails_for_missing_number(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->expectException(InvalidArgumentException::class);
        (new InvoiceImporter())->run(['customer_id' => 1]);
    }
}
