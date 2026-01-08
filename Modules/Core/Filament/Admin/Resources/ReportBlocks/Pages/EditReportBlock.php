<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\ReportBlockResource;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas\ReportBlockForm;
use Modules\Core\Models\ReportBlock;

class EditReportBlock extends Page
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ReportBlock $record;

    protected static string $resource = ReportBlockResource::class;

    protected string $view = 'core::filament.admin.resources.report-blocks.pages.edit-report-block';

    public function mount(int $record): void
    {
        $this->record = ReportBlock::query()->findOrFail($record);
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit Modal')
            ->schema(fn (Schema $schema) => ReportBlockForm::configure($schema))
            ->mountUsing(function (Schema $schema) {
                $data              = $this->record->toArray();
                $data['is_active'] = (bool) ($data['is_active'] ?? true);
                if (isset($data['width']) && $data['width'] instanceof BackedEnum) {
                    $data['width'] = $data['width']->value;
                }
                $schema->fill($data);
            })
            ->fillForm(function () {
                $data              = $this->record->toArray();
                $data['is_active'] = (bool) ($data['is_active'] ?? true);
                if (isset($data['width']) && $data['width'] instanceof BackedEnum) {
                    $data['width'] = $data['width']->value;
                }

                return $data;
            })
            ->action(function (array $data) {
                $this->record->update($data);
                $this->record->refresh();
            });
    }
}
