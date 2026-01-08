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
        $this->assertContains($address->address_type, array_column(AddressType::cases(), 'value'));
        
        // address_2 can be null or a string like "Apt 12"
        $this->assertTrue(
            is_null($address->address_2) || is_string($address->address_2),
            'address_2 should be null or a string'
        );
        
        // If address_2 is not null, it should match the pattern "Apt ##"
        // numerify('Apt ##') always generates exactly 2 digits (e.g., "Apt 05", "Apt 99")
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
            // numerify('Apt ##') always generates exactly 2 digits (e.g., "Apt 05", "Apt 99")
            if ($address->address_2 !== null) {
                $this->assertMatchesRegularExpression('/^Apt \d{2}$/', $address->address_2);
            }
        }
    }

    #[Test]
    public function it_generates_address_2_with_approximately_70_percent_probability(): void
    {
        /* Arrange - Create large sample for probability testing */
        $sampleSize = 100;

        /* Act */
        $addresses = Address::factory()->count($sampleSize)->make();

        /* Assert */
        $withAddress2 = $addresses->whereNotNull('address_2')->count();
        $percentage = ($withAddress2 / $sampleSize) * 100;

        // Allow 15% margin (55%-85% range) to account for randomness
        $this->assertGreaterThanOrEqual(55, $percentage, 'address_2 should be present in approximately 70% of cases');
        $this->assertLessThanOrEqual(85, $percentage, 'address_2 should be present in approximately 70% of cases');
    }
}
