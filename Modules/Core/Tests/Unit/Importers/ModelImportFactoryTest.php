<?php

namespace Modules\Core\Importers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModelImportFactoryTest extends TestCase
{
    #[Test]
    #[Group('support')]
    public function it_resolves_invoice_importer(): void
    {
        $importer = ImportFactory::resolve('invoices');
        $this->assertInstanceOf(InvoiceImporter::class, $importer);
    }

    #[Test]
    public function it_fails_for_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ImportFactory::resolve('nonsense');
    }
}
