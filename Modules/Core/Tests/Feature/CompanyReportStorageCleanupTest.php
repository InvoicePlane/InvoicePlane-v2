<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * RB-05 (#758) — per-company report storage lives on disk, not in the
 * database. Deleting a company must reap it.
 */
class CompanyReportStorageCleanupTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_removes_report_template_and_pdf_directories_when_a_company_is_deleted(): void
    {
        /* Arrange */
        Storage::fake(ReportTemplateStorage::DISK);
        Storage::fake('report_pdfs');
        $company = Company::factory()->create();
        Storage::disk(ReportTemplateStorage::DISK)->put("{$company->id}/custom/manifest.json", '{}');
        Storage::disk('report_pdfs')->put("{$company->id}/invoice-1.pdf", '%PDF');

        /* Act */
        $company->delete();

        /* Assert */
        $this->assertFalse(Storage::disk(ReportTemplateStorage::DISK)->exists((string) $company->id));
        $this->assertFalse(Storage::disk('report_pdfs')->exists((string) $company->id));
    }

    #[Test]
    public function it_leaves_other_companies_report_storage_intact(): void
    {
        /* Arrange */
        Storage::fake(ReportTemplateStorage::DISK);
        Storage::fake('report_pdfs');
        $doomed = Company::factory()->create();
        $kept   = Company::factory()->create();
        Storage::disk(ReportTemplateStorage::DISK)->put("{$doomed->id}/t/manifest.json", '{}');
        Storage::disk(ReportTemplateStorage::DISK)->put("{$kept->id}/t/manifest.json", '{}');
        Storage::disk('report_pdfs')->put("{$kept->id}/invoice-9.pdf", '%PDF');

        /* Act */
        $doomed->delete();

        /* Assert */
        $this->assertFalse(Storage::disk(ReportTemplateStorage::DISK)->exists((string) $doomed->id));
        $this->assertTrue(Storage::disk(ReportTemplateStorage::DISK)->exists("{$kept->id}/t/manifest.json"));
        $this->assertTrue(Storage::disk('report_pdfs')->exists("{$kept->id}/invoice-9.pdf"));
    }
}
