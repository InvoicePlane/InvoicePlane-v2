<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\ReportBlockResource;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas\ReportBlockForm;
use Modules\Core\Models\ReportBlock;

class EditReportBlock extends Page implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ReportBlock $record;

    protected static string $resource = ReportBlockResource::class;

    protected string $view = 'core::filament.admin.resources.report-blocks.pages.edit-report-block';

    public function mount(int $record): void
    {
        $this->record = ReportBlock::findOrFail($record);
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit Modal')
            ->form(fn (Schema $schema) => ReportBlockForm::configure($schema))
            ->fillForm($this->record->toArray())
            ->action(function (array $data) {
                $this->record->update($data);
                $this->record->refresh();
            });
    }
}
