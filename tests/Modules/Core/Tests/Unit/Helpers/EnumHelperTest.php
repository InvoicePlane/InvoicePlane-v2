<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

enum TestStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
}

class EnumHelperTest extends AbstractTestCase
{
    #[Test]
    public function it_returns_enum_values(): void
    {
        // act
        $result = EnumHelper::values(TestStatus::class);

        // assert
        $this->assertEquals(['active', 'inactive'], $result);
    }

    #[Test]
    public function it_returns_enum_labels(): void
    {
        // act
        $result = EnumHelper::labels(TestStatus::class);

        // assert
        $this->assertEquals(['Active', 'Inactive'], $result);
    }

    #[Test]
    public function it_handles_invalid_enum_gracefully(): void
    {
        // act
        $result = EnumHelper::labels('Invalid\Enum');

        // assert
        $this->assertEquals([], $result);
    }
}
