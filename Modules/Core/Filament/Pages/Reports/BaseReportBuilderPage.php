<?php

namespace Modules\Core\Filament\Pages\Reports;

use Awcodes\Mason\Mason;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Mason\MasonDocumentConverter;
use Modules\Core\Mason\ReportBrickAction;
use Modules\Core\Mason\ReportBricksCollection;
use Modules\Core\Services\ReportTemplateStorage;

/**
 * Shared five-band report builder page. One Mason canvas per band, each
 * offering only the bricks allowed in that band. Cross-band movement is a
 * discrete "move to band" action — never cross-canvas dragging.
 */
abstract class BaseReportBuilderPage extends Page implements HasForms
{
    use InteractsWithForms;

    public string $scope = '';

    public string $type = '';

    public string $templateSlug = '';

    public array $manifest = [];

    public array $data = [];

    protected static ?string $slug = 'report-builder/{scope}/{type}/{slug}';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'core::filament.pages.reports.report-builder';

    abstract public function managesSystemScope(): bool;

    /**
     * The fully-qualified class of this panel's template list page.
     */
    abstract public function listPage(): string;

    public function mount(string $scope, string $type, string $slug): void
    {
        abort_unless(in_array($scope, [ReportTemplateStorage::SCOPE_SYSTEM, ReportTemplateStorage::SCOPE_COMPANY], true), 404);

        $templateType = ReportTemplateType::tryFrom($type);
        abort_if($templateType === null, 404);

        if ($this->managesSystemScope() && $scope !== ReportTemplateStorage::SCOPE_SYSTEM) {
            abort(404);
        }

        $template = app(ReportTemplateStorage::class)->load($scope, $slug, $templateType);
        abort_if($template === null, 404);

        $this->scope        = $scope;
        $this->type         = $type;
        $this->templateSlug = $slug;
        $this->manifest     = $template['manifest'];

        $bands = [];

        foreach (ReportBand::ordered() as $band) {
            $bands[$band->value] = MasonDocumentConverter::toMasonState($template['bands'][$band->value] ?? []);
        }

        $this->form->fill(['bands' => $bands]);
    }

    public function getTitle(): string
    {
        return trans('ip.report_builder') . ': ' . ($this->manifest['name'] ?? $this->templateSlug);
    }

    public function form(Schema $schema): Schema
    {
        $fields = [];

        foreach (ReportBand::ordered() as $band) {
            $fields[] = Section::make($band->getLabel())
                ->collapsible()
                ->schema([
                    Mason::make('bands.' . $band->value)
                        ->hiddenLabel()
                        ->bricks(ReportBricksCollection::forBand($band))
                        ->registerActions([ReportBrickAction::make()])
                        ->disabled( ! $this->canSave()),
                ]);
        }

        return $schema->components($fields)->statePath('data');
    }

    public function canSave(): bool
    {
        return $this->managesSystemScope()
            ? $this->scope === ReportTemplateStorage::SCOPE_SYSTEM
            : $this->scope === ReportTemplateStorage::SCOPE_COMPANY;
    }

    public function save(): void
    {
        abort_unless($this->canSave(), 403);

        $state = $this->form->getState();
        $bands = [];

        foreach (ReportBand::ordered() as $band) {
            $bands[$band->value] = MasonDocumentConverter::toBandEntries($state['bands'][$band->value] ?? []);
        }

        app(ReportTemplateStorage::class)->save(
            $this->scope,
            $this->templateSlug,
            $this->manifest,
            $bands,
            ReportTemplateType::tryFrom($this->type),
        );

        Notification::make()->title(trans('ip.template_saved'))->success()->send();
    }

    public function previewAction(): Action
    {
        return Action::make('preview')
            ->label(trans('ip.report_preview'))
            ->icon('heroicon-o-eye')
            ->modalHeading(trans('ip.report_preview'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(trans('ip.close'))
            ->slideOver()
            ->modalContent(fn (): HtmlString => new HtmlString($this->renderPreviewHtml()));
    }

    public function moveBrickAction(): Action
    {
        return Action::make('moveBrick')
            ->label(trans('ip.move_to_band'))
            ->icon('heroicon-o-arrows-up-down')
            ->visible(fn (): bool => $this->canSave())
            ->schema([
                Select::make('from_band')
                    ->label(trans('ip.from_band'))
                    ->options($this->bandOptions())
                    ->required()
                    ->live()
                    /*
                     * Both dependent selects have to be cleared by hand.
                     * Filament recomputes their options but keeps whatever
                     * was picked before, so a stale index would silently
                     * move a different brick than the one on screen.
                     */
                    ->afterStateUpdated(function (Set $set): void {
                        $set('position', null);
                        $set('to_band', null);
                    }),
                Select::make('position')
                    ->label(trans('ip.brick'))
                    ->options(function (Get $get): array {
                        return $this->brickOptionsForBand((string) $get('from_band'));
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('to_band', null)),
                Select::make('to_band')
                    ->label(trans('ip.to_band'))
                    ->options(function (Get $get): array {
                        return $this->targetBandOptions((string) $get('from_band'), $get('position'));
                    })
                    ->required(),
            ])
            ->action(function (array $data): void {
                $this->moveBrick((string) $data['from_band'], (int) $data['position'], (string) $data['to_band']);
            });
    }

    public function moveBrick(string $fromBand, int $position, string $toBand): void
    {
        abort_unless($this->canSave(), 403);

        $source = $this->data['bands'][$fromBand] ?? [];

        if ($fromBand === $toBand || ! isset($source[$position])) {
            return;
        }

        $node       = $source[$position];
        $brickClass = ReportBricksCollection::findById((string) ($node['attrs']['id'] ?? ''));
        $target     = ReportBand::tryFrom($toBand);

        if ($brickClass === null || $target === null || ! in_array($target, $brickClass::allowedBands(), true)) {
            Notification::make()->title(trans('ip.brick_not_allowed_in_band'))->danger()->send();

            return;
        }

        array_splice($source, $position, 1);

        $this->data['bands'][$fromBand] = array_values($source);
        $this->data['bands'][$toBand][] = $node;
        $this->data['bands'][$toBand]   = array_values($this->data['bands'][$toBand]);

        Notification::make()->title(trans('ip.brick_moved'))->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->previewAction(),
            $this->moveBrickAction(),
            Action::make('save')
                ->label(trans('ip.save'))
                ->visible(fn (): bool => $this->canSave())
                ->action('save'),
        ];
    }

    protected function renderPreviewHtml(): string
    {
        $html = '';

        foreach (ReportBand::ordered() as $band) {
            $bandHtml = '';

            foreach (MasonDocumentConverter::toBandEntries($this->data['bands'][$band->value] ?? []) as $entry) {
                $brickClass = ReportBricksCollection::findById($entry['brick']);

                if ($brickClass === null) {
                    continue;
                }

                $config = $entry['config'];
                // Preserve width for preview rendering
                if (!isset($config[MasonDocumentConverter::WIDTH_KEY])) {
                    $config[MasonDocumentConverter::WIDTH_KEY] = $entry['width'] ?? 'full';
                }

                $width = ReportBlockWidth::tryFrom((string) ($config[MasonDocumentConverter::WIDTH_KEY] ?? '')) ?? ReportBlockWidth::FULL;
                $percent = match ($width) {
                    ReportBlockWidth::ONE_THIRD  => '33.33%',
                    ReportBlockWidth::HALF       => '50%',
                    ReportBlockWidth::TWO_THIRDS => '66.66%',
                    ReportBlockWidth::FULL       => '100%',
                };

                $previewHtml = (string) $brickClass::toPreviewHtml($config);
                $bandHtml .= '<div style="flex: 0 0 ' . $percent . '; max-width: ' . $percent . '; padding-right: 8px; box-sizing: border-box;">' . $previewHtml . '</div>';
            }

            if ($bandHtml) {
                $html .= '<div style="display: flex; flex-wrap: wrap; gap: 0; margin-bottom: 16px;">' . $bandHtml . '</div>';
            }
        }

        return $html;
    }

    /**
     * @return array<string, string>
     */
    protected function bandOptions(): array
    {
        $options = [];

        foreach (ReportBand::ordered() as $band) {
            $options[$band->value] = $band->getLabel();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    protected function brickOptionsForBand(string $bandValue): array
    {
        $options = [];

        foreach ($this->data['bands'][$bandValue] ?? [] as $index => $node) {
            $brickClass = ReportBricksCollection::findById((string) ($node['attrs']['id'] ?? ''));

            if ($brickClass !== null) {
                $options[$index] = ($index + 1) . '. ' . $brickClass::getLabel();
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected function targetBandOptions(string $fromBand, mixed $position): array
    {
        // No brick picked yet — casting null to 0 would offer the bands of
        // whichever brick happens to sit first in the source band.
        if ($position === null || $position === '') {
            return [];
        }

        $node       = $this->data['bands'][$fromBand][(int) $position] ?? null;
        $brickClass = $node ? ReportBricksCollection::findById((string) ($node['attrs']['id'] ?? '')) : null;

        if ($brickClass === null) {
            return [];
        }

        $options = [];

        foreach ($brickClass::allowedBands() as $band) {
            if ($band->value !== $fromBand) {
                $options[$band->value] = $band->getLabel();
            }
        }

        return $options;
    }
}
