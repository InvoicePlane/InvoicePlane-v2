<?php

namespace Modules\Projects\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TasksExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_with_no_records(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_with_special_characters(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
    }
}
