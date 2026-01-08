<?php

namespace Modules\Clients\Tests\Unit;

use Modules\Clients\Models\Address;
use Modules\Core\Enums\AddressType;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class AddressFactoryTest extends AbstractTestCase
{
    #[Test]
    public function it_creates_address_with_valid_secondary_address(): void
    {
        /* Arrange - Factory is configured */

        /* Act */
        $address = Address::factory()->make();

        /* Assert */
        $this->assertInstanceOf(Address::class, $address);
        $this->assertNotNull($address->address_1);
        $this->assertContains($address->address_type, array_map(fn($case) => $case->value, AddressType::cases()));
        
        // address_2 can be null or a string like "Apt 12"
        $this->assertTrue(
            is_null($address->address_2) || is_string($address->address_2),
            'address_2 should be null or a string'
        );
        
        // If address_2 is not null, it should match the pattern "Apt ##"
        if ($address->address_2 !== null) {
            $this->assertMatchesRegularExpression('/^Apt \d{2}$/', $address->address_2);
        }
    }

    #[Test]
    public function it_generates_multiple_addresses_without_errors(): void
    {
        /* Arrange - Set up to create multiple addresses */
        $count = 10;

        /* Act */
        $addresses = Address::factory()->count($count)->make();

        /* Assert */
        $this->assertCount($count, $addresses);
        
        foreach ($addresses as $address) {
            $this->assertInstanceOf(Address::class, $address);
            $this->assertNotNull($address->address_1);
            
            // Verify address_2 is either null or matches expected pattern
            if ($address->address_2 !== null) {
                $this->assertMatchesRegularExpression('/^Apt \d{2}$/', $address->address_2);
            }
        }
    }
}
