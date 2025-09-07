<?php

namespace Modules\Projects\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProjectsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_with_no_records(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_with_special_characters(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
    }
}
