<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Modules\Core\Models\Address;
use Modules\Core\Models\AddressHelper;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class AddressTest extends AbstractTestCase
{
    #[Test]
    /**
     * Find Legacy Function.
     */
    #[Group('crud')]
    public function it_formats_address_correctly(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $data = [
            'street'  => 'Main St.',
            'number'  => '42',
            'zip'     => '12345',
            'city'    => 'Invoiceville',
            'country' => 'Netherlands',
        ];

        // act
        $formatted = AddressHelper::formatAddress($data);

        // assert
        $this->assertEquals("Main St. 42\n12345 Invoiceville\nNetherlands", $formatted);
    }

    #[Test]
    public function it_handles_missing_fields_gracefully(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $data = [
            'street'  => null,
            'number'  => null,
            'zip'     => null,
            'city'    => null,
            'country' => null,
        ];

        // act
        $formatted = Address::format($data);

        // assert
        $this->assertEquals('', $formatted);
    }
}
