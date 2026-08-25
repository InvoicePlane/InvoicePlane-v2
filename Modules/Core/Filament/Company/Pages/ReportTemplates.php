<?php

namespace Modules\Core\Filament\Company\Pages;

use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Pages\Reports\BaseReportTemplatesPage;

class ReportTemplates extends BaseReportTemplatesPage
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            ...UserRole::elevated(),
            UserRole::CUSTOMER_ADMIN->value,
        ]) ?? false;
    }

    public function managesSystemScope(): bool
    {
        return false;
    }

    public function builderPage(): string
    {
        return ReportBuilder::class;
    }
}
