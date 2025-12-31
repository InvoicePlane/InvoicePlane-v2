<?php

namespace Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Services\GridSnapperService;
use Modules\ReportBuilder\Services\ReportTemplateService;
use Modules\ReportBuilder\Transformers\BlockTransformer;

class DesignReportTemplate extends Page
{
    public ReportTemplate $record;

    public array $blocks = [];

    public string $selectedBlockId = '';

    protected static string $resource = ReportTemplateResource::class;

    protected string $view = 'reportbuilder::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function mount(ReportTemplate $record): void
    {
        $this->record = $record;
        $this->loadBlocks();
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
    }

    #[On('add-block')]
    public function addBlock(string $blockType): void
    {
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
        $this->redirect(static::getResource()::getUrl('index'));
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
