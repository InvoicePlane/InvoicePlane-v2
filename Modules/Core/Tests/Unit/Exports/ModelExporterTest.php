<?php

namespace Modules\Core\Tests\Unit\Exports;

use Modules\Core\Exports\ModelExport;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ModelExporterTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_exports_collection_to_csv(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $data = collect([
            ['id' => 1, 'name' => 'Test'],
            ['id' => 2, 'name' => 'Example'],
        ]);

        $csv = ModelExport::toCsv($data);
        $this->assertStringContainsString('id,name', $csv);
        $this->assertStringContainsString('1,Test', $csv);
    }
}
