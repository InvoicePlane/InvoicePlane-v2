<?php

namespace Modules\Core\Filament\Pages\Reports;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Services\ReportTemplateStorage;

/**
 * Shared template list page: shows system templates and (in the company
 * panel) the current company's clones. Panel subclasses decide which scope
 * is editable and where the builder lives.
 */
abstract class BaseReportTemplatesPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    protected string $view = 'core::filament.pages.reports.report-templates';

    /**
     * Whether this panel manages the system scope (admin) or the company
     * scope (company panel).
     */
    abstract public function managesSystemScope(): bool;

    /**
     * The fully-qualified class of this panel's builder page.
     */
    abstract public function builderPage(): string;

    public static function getNavigationLabel(): string
    {
        return trans('ip.report_templates');
    }

    public function getTitle(): string
    {
        return trans('ip.report_templates');
    }

    /**
     * @return array<int, array{scope: string, type: string, slug: string, manifest: array, editable: bool}>
     */
    public function getTemplates(): array
    {
        $storage   = $this->storage();
        $templates = [];

        foreach ($storage->listSystem() as $template) {
            $template['editable'] = $this->managesSystemScope();
            $templates[]          = $template;
        }

        if ( ! $this->managesSystemScope()) {
            foreach ($storage->listCompany() as $template) {
                $template['editable'] = true;
                $templates[]          = $template;
            }
        }

        return $templates;
    }

    public function builderUrl(array $template): ?string
    {
        if ( ! $template['editable']) {
            return null;
        }

        return $this->builderPage()::getUrl([
            'scope' => $template['scope'],
            'type'  => $template['type'],
            'slug'  => $template['slug'],
        ]);
    }

    public function cloneAction(): Action
    {
        return Action::make('clone')
            ->label(trans('ip.clone'))
            ->icon('heroicon-o-document-duplicate')
            ->schema([
                TextInput::make('name')
                    ->label(trans('ip.name'))
                    ->required()
                    ->maxLength(100),
            ])
            ->action(function (array $arguments, array $data): void {
                $clone = $this->storage()->clone(
                    (string) $arguments['scope'],
                    (string) $arguments['slug'],
                    (string) $data['name'],
                    ReportTemplateType::tryFrom((string) $arguments['type']),
                    $this->managesSystemScope() ? ReportTemplateStorage::SCOPE_SYSTEM : ReportTemplateStorage::SCOPE_COMPANY,
                );

                Notification::make()
                    ->title(trans('ip.template_cloned'))
                    ->body($clone['manifest']['name'])
                    ->success()
                    ->send();
            });
    }

    public function renameAction(): Action
    {
        return Action::make('rename')
            ->label(trans('ip.rename'))
            ->icon('heroicon-o-pencil-square')
            ->fillForm(fn (array $arguments): array => ['name' => $arguments['name'] ?? ''])
            ->schema([
                TextInput::make('name')
                    ->label(trans('ip.name'))
                    ->required()
                    ->maxLength(100),
            ])
            ->action(function (array $arguments, array $data): void {
                $this->storage()->rename(
                    (string) $arguments['scope'],
                    (string) $arguments['slug'],
                    (string) $data['name'],
                    ReportTemplateType::tryFrom((string) $arguments['type']),
                );

                Notification::make()->title(trans('ip.template_renamed'))->success()->send();
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(trans('ip.delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                $this->storage()->delete(
                    (string) $arguments['scope'],
                    (string) $arguments['slug'],
                    ReportTemplateType::tryFrom((string) $arguments['type']),
                );

                Notification::make()->title(trans('ip.template_deleted'))->success()->send();
            });
    }

    public function canModify(array $template): bool
    {
        if ( ! $template['editable']) {
            return false;
        }

        return ! ($template['scope'] === ReportTemplateStorage::SCOPE_SYSTEM && $template['slug'] === 'default');
    }

    protected function storage(): ReportTemplateStorage
    {
        return app(ReportTemplateStorage::class);
    }
}
