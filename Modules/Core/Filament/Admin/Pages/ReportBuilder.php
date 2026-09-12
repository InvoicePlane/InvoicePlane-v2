<?php

namespace Modules\Core\Filament\Admin\Pages;

use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Pages\Reports\BaseReportBuilderPage;

class ReportBuilder extends BaseReportBuilderPage
{
    public static function canAccess(): bool
    {
        // Deliberately excludes ASSIST because system templates are instance-global.
        return auth()->user()?->isSuperAdmin() || (auth()->user()?->hasRole(UserRole::ADMIN->value) ?? false);
    }

    public function managesSystemScope(): bool
    {
        return true;
    }

    public function listPage(): string
    {
        return ReportTemplates::class;
    }
}
