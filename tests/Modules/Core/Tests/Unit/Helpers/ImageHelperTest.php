<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ImageHelperTest extends AbstractTestCase
{
    #[Test]
    public function it_generates_expected_path(): void
    {
        $this->markTestIncomplete();

        // arrange
        $filename = 'avatar.png';

        // act
        $path = ImageHelper::path($filename);

        // assert
        $this->assertStringContainsString('storage/images/avatar.png', $path);
    }

    #[Test]
    public function it_returns_null_for_empty_filename(): void
    {
        $this->markTestIncomplete();

        // act
        $path = ImageHelper::path('');

        // assert
        $this->assertNull($path);
    }
}
