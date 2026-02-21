<?php

namespace Modules\Core\Tests\Unit\Services;

use Modules\Core\Services\ImportInvoicePlaneV1Service;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ImportInvoicePlaneV1ServiceTest extends AbstractTestCase
{
    private ImportInvoicePlaneV1Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ImportInvoicePlaneV1Service();
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        /* Assert */
        $this->assertInstanceOf(ImportInvoicePlaneV1Service::class, $this->service);
    }

    #[Test]
    public function it_has_correct_temp_database_name(): void
    {
        /* Arrange */
        $reflection = new \ReflectionClass($this->service);
        $constant = $reflection->getConstant('TEMP_DB_NAME');

        /* Assert */
        $this->assertEquals('invoiceplane_v1_temp', $constant);
    }
}
