<?php

namespace Modules\Invoices\Tests\Unit\Services;

use Exception;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\InvoiceNumberService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceNumberServiceTest extends AbstractTestCase
{
    /**
     * @payload ["groupId" => $group->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_generates_formatted_number(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $group   = DocumentGroup::factory()->create(['left_pad' => 'INV', 'next_number' => 100]);
        $service = new InvoiceNumberService();
        $number  = $service->generate($group->id);
        if (app()->isLocal()) {
            dump($number);
        }
        $this->assertStringStartsWith('INV-100', $number);
    }

    /**
     * @payload ["groupId" => 0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_when_group_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $service = new InvoiceNumberService();
        $this->expectException(Exception::class);
        $service->generate(0);
    }
}
