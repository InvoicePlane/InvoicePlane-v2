<?php

namespace Modules\Core\Tests\Unit\Importers;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ModelImportFactoryTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_resolves_invoice_importer(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $importer = ImportFactory::resolve('invoices');
        $this->assertInstanceOf(InvoiceImporter::class, $importer);
    }

    #[Test]
    public function it_fails_for_unknown_type(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->expectException(InvalidArgumentException::class);
        ImportFactory::resolve('nonsense');
    }
}
