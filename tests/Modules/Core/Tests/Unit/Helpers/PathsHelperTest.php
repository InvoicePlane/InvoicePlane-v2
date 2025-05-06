<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class PathsHelperTest extends AbstractTestCase
{
    #[Test]
    public function it_returns_storage_path_for_uploads(): void
    {
        // act
        $path = PathsHelper::uploadPath('invoices');

        // assert
        $this->assertStringContainsString('/storage/uploads/invoices', $path);
    }

    #[Test]
    public function it_handles_null_directory(): void
    {
        // act
        $path = PathsHelper::uploadPath(null);

        // assert
        $this->assertStringContainsString('/storage/uploads', $path);
    }
}
