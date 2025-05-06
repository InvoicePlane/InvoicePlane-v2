<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Modules\Core\Helpers\TempFileCleanupHelper;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class TempFileCleanupHelperTest extends AbstractTestCase
{
    #[Test]
    public function it_skips_cleanup_if_path_is_missing(): void
    {
        // act
        $result = TempFileCleanupHelper::clean('');

        // assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_handles_nonexistent_directory(): void
    {
        // arrange
        $nonexistent = '/tmp/this_should_not_exist';

        // act
        $result = TempFileCleanupHelper::clean($nonexistent);

        // assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_cleans_temp_files_if_valid(): void
    {
        // arrange
        $dir = storage_path('app/testing/temp-cleanup');
        File::ensureDirectoryExists($dir);
        File::put($dir . '/temp.txt', 'delete me');

        // act
        $result = TempFileCleanupHelper::clean($dir);

        // assert
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($dir . '/temp.txt');
    }
}
