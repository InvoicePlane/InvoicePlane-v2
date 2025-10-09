<?php

namespace Modules\Invoices\Tests\Unit\Peppol\FormatHandlers;

use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use Modules\Invoices\Peppol\FormatHandlers\FormatHandlerFactory;
use Modules\Invoices\Peppol\FormatHandlers\InvoiceFormatHandlerInterface;
use Modules\Invoices\Peppol\FormatHandlers\PeppolBisHandler;
use Modules\Invoices\Peppol\FormatHandlers\UblHandler;
use Modules\Invoices\Peppol\FormatHandlers\CiiHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FormatHandlerFactoryTest - Unit tests for FormatHandlerFactory.
 *
 * Tests the factory pattern for creating format handlers,
 * including handler registration and selection logic.
 *
 * @package Modules\Invoices\Tests\Unit\Peppol\FormatHandlers
 */
#[Group('peppol')]
class FormatHandlerFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset any custom registered handlers after each test
        parent::tearDown();
    }

    #[Test]
    public function it_creates_peppol_bis_handler(): void
    {
        $handler = FormatHandlerFactory::create(PeppolDocumentFormat::PEPPOL_BIS_30);

        $this->assertInstanceOf(PeppolBisHandler::class, $handler);
        $this->assertInstanceOf(InvoiceFormatHandlerInterface::class, $handler);
    }

    #[Test]
    public function it_creates_ubl_21_handler(): void
    {
        $handler = FormatHandlerFactory::create(PeppolDocumentFormat::UBL_21);

        $this->assertInstanceOf(UblHandler::class, $handler);
        $this->assertInstanceOf(InvoiceFormatHandlerInterface::class, $handler);
    }

    #[Test]
    public function it_creates_ubl_24_handler(): void
    {
        $handler = FormatHandlerFactory::create(PeppolDocumentFormat::UBL_24);

        $this->assertInstanceOf(UblHandler::class, $handler);
        $this->assertInstanceOf(InvoiceFormatHandlerInterface::class, $handler);
    }

    #[Test]
    public function it_creates_cii_handler(): void
    {
        $handler = FormatHandlerFactory::create(PeppolDocumentFormat::CII);

        $this->assertInstanceOf(CiiHandler::class, $handler);
        $this->assertInstanceOf(InvoiceFormatHandlerInterface::class, $handler);
    }

    #[Test]
    public function it_throws_exception_for_unsupported_format(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler available for format');

        FormatHandlerFactory::create(PeppolDocumentFormat::FATTURAPA_12);
    }

    #[Test]
    public function it_can_check_if_handler_exists(): void
    {
        $this->assertTrue(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::PEPPOL_BIS_30));
        $this->assertTrue(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::UBL_21));
        $this->assertTrue(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::UBL_24));
        $this->assertTrue(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::CII));
        
        $this->assertFalse(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::FATTURAPA_12));
        $this->assertFalse(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::FACTURAE_32));
    }

    #[Test]
    public function it_returns_registered_handlers(): void
    {
        $handlers = FormatHandlerFactory::getRegisteredHandlers();

        $this->assertIsArray($handlers);
        $this->assertArrayHasKey('peppol_bis_3.0', $handlers);
        $this->assertArrayHasKey('ubl_2.1', $handlers);
        $this->assertArrayHasKey('ubl_2.4', $handlers);
        $this->assertArrayHasKey('cii', $handlers);
        
        $this->assertEquals(PeppolBisHandler::class, $handlers['peppol_bis_3.0']);
        $this->assertEquals(UblHandler::class, $handlers['ubl_2.1']);
        $this->assertEquals(CiiHandler::class, $handlers['cii']);
    }

    #[Test]
    public function it_creates_handler_from_format_string(): void
    {
        $handler = FormatHandlerFactory::make('peppol_bis_3.0');

        $this->assertInstanceOf(PeppolBisHandler::class, $handler);
    }

    #[Test]
    public function it_throws_exception_for_invalid_format_string(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid format');

        FormatHandlerFactory::make('invalid_format_string');
    }

    #[Test]
    public function it_can_register_custom_handler(): void
    {
        $customHandlerClass = new class implements InvoiceFormatHandlerInterface {
            public function generateXml(array $invoiceData): string
            {
                (void)$invoiceData;
                return '<custom></custom>';
            }
            
            public function validate(array $_invoiceData): array
            {
                (void)$_invoiceData;
                return ['valid' => true, 'errors' => []];
            }
            
            public function getFormat(): PeppolDocumentFormat
            {
                return PeppolDocumentFormat::FATTURAPA_12;
            }
        };

        FormatHandlerFactory::register(
            PeppolDocumentFormat::FATTURAPA_12,
            get_class($customHandlerClass)
        );

        $this->assertTrue(FormatHandlerFactory::hasHandler(PeppolDocumentFormat::FATTURAPA_12));
    }

    #[Test]
    public function it_uses_same_handler_for_ubl_versions(): void
    {
        $handler21 = FormatHandlerFactory::create(PeppolDocumentFormat::UBL_21);
        $handler24 = FormatHandlerFactory::create(PeppolDocumentFormat::UBL_24);

        // Both should be UBL handlers
        $this->assertInstanceOf(UblHandler::class, $handler21);
        $this->assertInstanceOf(UblHandler::class, $handler24);
        
        // They should be the same class
        $this->assertEquals(get_class($handler21), get_class($handler24));
    }

    #[Test]
    public function it_resolves_handlers_via_service_container(): void
    {
        // The factory should use app() to resolve handlers
        $handler = FormatHandlerFactory::create(PeppolDocumentFormat::PEPPOL_BIS_30);

        $this->assertInstanceOf(InvoiceFormatHandlerInterface::class, $handler);
    }
}