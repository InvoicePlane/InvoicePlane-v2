<?php

namespace Modules\Core\Filament\Admin\Pages;

use Modules\Core\Filament\Pages\Reports\BaseReportBuilderPage;

class ReportBuilder extends BaseReportBuilderPage
{
    public function managesSystemScope(): bool
    {
        return true;
    }

    public function listPage(): string
    {
        return ReportTemplates::class;
    }
}
