<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Enums\Permission;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Enums\ExpenseStatus;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class EnumLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function it_resolves_enum_labels_in_the_current_locale_without_changing_storage_values(): void
    {
        app('translator')->addLines(['enums.expense_status.draft' => 'Entwurf Test', 'enums.report_band.header' => 'Kopf Test'], 'de');
        app()->setLocale('en');
        $value = ExpenseStatus::DRAFT->value;
        $this->assertSame('Draft', ExpenseStatus::DRAFT->label());
        app()->setLocale('de');
        $this->assertSame('Entwurf Test', ExpenseStatus::DRAFT->label());
        $this->assertSame('Kopf Test', ReportBand::HEADER->getLabel());
        $this->assertSame($value, ExpenseStatus::DRAFT->value);
        app()->setLocale('en');
        $this->assertSame('Draft', ExpenseStatus::DRAFT->label());
    }

    #[Test]
    public function it_translates_permissions_without_changing_the_permission_identifiers(): void
    {
        app('translator')->addLines(['permissions.view-invoices' => 'Rechnungen anzeigen Test'], 'de');
        app()->setLocale('en');
        $this->assertSame('View Invoices', Permission::VIEW_INVOICES->label());
        app()->setLocale('de');
        $this->assertSame('Rechnungen anzeigen Test', Permission::VIEW_INVOICES->label());
        $this->assertSame('view-invoices', Permission::VIEW_INVOICES->value);
    }
}
