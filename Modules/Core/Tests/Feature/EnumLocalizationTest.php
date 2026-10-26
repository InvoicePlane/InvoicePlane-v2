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
        /* Arrange */
        app('translator')->addLines(['enums.expense_status.draft' => 'Entwurf Test', 'enums.report_band.header' => 'Kopf Test'], 'de');
        app()->setLocale('en');
        $value = ExpenseStatus::DRAFT->value;

        /* Act */
        $englishLabel = ExpenseStatus::DRAFT->label();
        app()->setLocale('de');
        $germanLabel = ExpenseStatus::DRAFT->label();
        $headerLabel = ReportBand::HEADER->getLabel();
        $germanValue = ExpenseStatus::DRAFT->value;
        app()->setLocale('en');
        $restoredEnglishLabel = ExpenseStatus::DRAFT->label();

        /* Assert */
        $this->assertSame('Draft', $englishLabel);
        $this->assertSame('Entwurf Test', $germanLabel);
        $this->assertSame('Kopf Test', $headerLabel);
        $this->assertSame($value, $germanValue);
        $this->assertSame('Draft', $restoredEnglishLabel);
    }

    #[Test]
    public function it_translates_permissions_without_changing_the_permission_identifiers(): void
    {
        /* Arrange */
        app('translator')->addLines(['permissions.view-invoices' => 'Rechnungen anzeigen Test'], 'de');
        app()->setLocale('en');

        /* Act */
        $englishLabel = Permission::VIEW_INVOICES->label();
        app()->setLocale('de');
        $germanLabel          = Permission::VIEW_INVOICES->label();
        $permissionIdentifier = Permission::VIEW_INVOICES->value;

        /* Assert */
        $this->assertSame('View Invoices', $englishLabel);
        $this->assertSame('Rechnungen anzeigen Test', $germanLabel);
        $this->assertSame('view-invoices', $permissionIdentifier);
    }
}
