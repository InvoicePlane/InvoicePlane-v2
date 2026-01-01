<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\ReportTemplateResource;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\GridSnapperService;
use Modules\Core\Services\ReportTemplateService;
use Modules\Core\Transformers\BlockTransformer;

class ReportBuilder extends Page
{
    public ReportTemplate $record;

    public array $blocks = [];

    public string $selectedBlockId = '';

    /**
     * The template filename, loaded from the database (or model).
     *
     * @var string|null
     */
    protected ?string $templateFilename = null;

    protected static string $resource = ReportTemplateResource::class;

    protected string $view = 'core::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function mount(ReportTemplate $record): void
    {
        $this->record           = $record;
        $this->templateFilename = $this->getTemplateFilenameFromDatabase();
        $this->loadBlocks();
        $this->loadTemplate();
    }

    #[On('drag-block')]
    public function updateBlockPosition(string $blockId, array $position): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $gridSnapper = app(GridSnapperService::class);
        $positionDTO = GridPositionDTO::create(
            $position['x'] ?? 0,
            $position['y'] ?? 0,
            $position['width'] ?? 1,
            $position['height'] ?? 1
        );

        if ( ! $gridSnapper->validate($positionDTO)) {
            return;
        }

        $snappedPosition = $gridSnapper->snap($positionDTO);

        $this->blocks[$blockId]['position'] = [
            'x'      => $snappedPosition->getX(),
            'y'      => $snappedPosition->getY(),
            'width'  => $snappedPosition->getWidth(),
            'height' => $snappedPosition->getHeight(),
        ];

        if (isset($position['band'])) {
            $this->blocks[$blockId]['band'] = $position['band'];
        }
    }

    #[On('add-block')]
    public function addBlock(string $blockType): void
    {
        $service      = app(ReportTemplateService::class);
        $systemBlocks = $service->getSystemBlocks();

        if (isset($systemBlocks[$blockType])) {
            $blockDto = $systemBlocks[$blockType];
            $blockId  = 'block_' . $blockType . '_' . Str::random(8);
            $blockDto->setId($blockId);

            $this->blocks[$blockId] = BlockTransformer::toArray($blockDto);

            return;
        }

        $blockId = 'block_' . $blockType . '_' . Str::random(8);

        $position = GridPositionDTO::create(0, 0, 6, 4);

        $block = new BlockDTO();
        $block->setId($blockId)
            ->setType($blockType)
            ->setPosition($position)
            ->setConfig([])
            ->setLabel(ucfirst(str_replace('_', ' ', $blockType)))
            ->setIsCloneable(false)
            ->setDataSource('custom')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $this->blocks[$blockId] = BlockTransformer::toArray($block);
    }

    #[On('clone-block')]
    public function cloneBlock(string $blockId): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $originalBlock = $this->blocks[$blockId];

        if ($originalBlock['isCloned'] === false && $originalBlock['isCloneable'] === true) {
            $newBlockId = 'block_' . $originalBlock['type'] . '_' . Str::random(8);

            $position = GridPositionDTO::create(
                $originalBlock['position']['x'] + 1,
                $originalBlock['position']['y'] + 1,
                $originalBlock['position']['width'],
                $originalBlock['position']['height']
            );

            $clonedBlock = new BlockDTO();
            $clonedBlock->setId($newBlockId)
                ->setType($originalBlock['type'])
                ->setPosition($position)
                ->setConfig($originalBlock['config'])
                ->setLabel($originalBlock['label'] . ' (Clone)')
                ->setIsCloneable(false)
                ->setDataSource($originalBlock['dataSource'])
                ->setIsCloned(true)
                ->setClonedFrom($blockId);

            $this->blocks[$newBlockId] = BlockTransformer::toArray($clonedBlock);
        }
    }

    #[On('delete-block')]
    public function deleteBlock(string $blockId): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        unset($this->blocks[$blockId]);
    }

    #[On('edit-config')]
    public function updateBlockConfig(string $blockId, array $config): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $this->blocks[$blockId]['config'] = array_replace_recursive(
            $this->blocks[$blockId]['config'] ?? [],
            $config
        );
    }

    public function save(): void
    {
        $service = app(ReportTemplateService::class);
        $service->persistBlocks($this->record, $this->blocks);

        $this->dispatch('blocks-saved');
        // Stay on the design page after saving
        // $this->redirect(static::getResource()::getUrl('index'));
    }

    public function saveTemplate($bands): void
    {
        // Flatten bands into blocks with band assignment
        $blocks = [];
        foreach ($bands as $bandIdx => $band) {
            if ( ! isset($band['blocks'])) {
                continue;
            }
            foreach ($band['blocks'] as $block) {
                $block['band']        = $band['name'] ?? (string) $bandIdx;
                $blocks[$block['id']] = $block;
            }
        }
        $this->blocks = $blocks;
        $service      = app(ReportTemplateService::class);
        $service->persistBlocks($this->record, $this->blocks);
        $this->dispatch('blocks-saved');
    }

    /**
     * Loads the template blocks from the filesystem using the template filename.
     */
    protected function loadTemplate(): void
    {
        if ( ! $this->templateFilename) {
            return;
        }
        $templatePath = 'report_templates/' . $this->templateFilename;
        if (Storage::disk('local')->exists($templatePath)) {
            $json   = Storage::disk('local')->get($templatePath);
            $blocks = json_decode($json, true);
            if (is_array($blocks)) {
                $this->blocks = [];
                foreach ($blocks as $block) {
                    $this->blocks[$block['id']] = $block;
                }
            }
        }
    }

    /**
     * Example method to get the template filename from the database/model.
     * Replace this with actual logic to retrieve the filename for the current template/record.
     */
    protected function getTemplateFilenameFromDatabase(): ?string
    {
        // Example: assuming $this->record has a 'template_filename' attribute
        return $this->record->template_filename ?? null;
    }

    protected function loadBlocks(): void
    {
        $service   = app(ReportTemplateService::class);
        $blockDTOs = $service->loadBlocks($this->record);

        $this->blocks = [];
        foreach ($blockDTOs as $blockDTO) {
            $blockArray                      = BlockTransformer::toArray($blockDTO);
            $this->blocks[$blockArray['id']] = $blockArray;
        }
    }
}
