<?php

namespace Modules\Core\Tests\Unit\Services;

use Exception;
use Modules\Core\Services\QrCodeGeneratorService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QrCodeGeneratorServiceTest extends AbstractTestCase
{
    /**
     * @payload ["data" => "https://app.test"]
     */
    #[Test]
    #[Group('spicy')]
    public function it_generates_base64_png_qr_code(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $service = new QrCodeGeneratorService();
        $qr      = $service->generate('https://app.test');
        if (app()->isLocal()) {
            dump($qr);
        }
        $this->assertIsString($qr);
        $this->assertStringStartsWith('data:image/png;base64,', $qr);
    }

    /**
     * @payload ["data" => ""]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_on_empty_input(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $service = new QrCodeGeneratorService();
        $this->expectException(Exception::class);
        $service->generate('');
    }
}
