<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TestTest extends AbstractTestCase
{
    #[Test]
    #[Group('testing')]
    /**
     * @payload ['relation_id' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe', 'gender' => 'female']
     */
    public function it_lists_contacts(): void
    {
        $this->assertTrue(true);
    }
}
