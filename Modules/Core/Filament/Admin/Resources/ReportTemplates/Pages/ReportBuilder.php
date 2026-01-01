<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas\ReportBlockForm;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\ReportTemplateResource;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\GridSnapperService;
use Modules\Core\Services\ReportTemplateService;
use Modules\Core\Transformers\BlockTransformer;

class ReportBuilder extends Page
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ReportTemplate $record;

    public array $blocks = [];

    public string $selectedBlockId = '';

    protected static string $resource = ReportTemplateResource::class;

    protected string $view = 'core::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function mount(ReportTemplate $record): void
    {
        $this->record = $record;
        $this->loadBlocks();
    }

    public function configureBlockAction(): Action
    {
        return Action::make('configureBlock')
            ->schema(fn (Schema $schema) => ReportBlockForm::configure($schema))
            ->fillForm(function (array $arguments) {
                $blockType = $arguments['blockType'] ?? null;
                if ( ! $blockType) {
                    return [];
                }

                // Try to find the block in our local blocks array first if it has specific config
                // but wait, $this->blocks is currently used for layout.
                // The global ReportBlock is the source of truth for the block definition.

                $block = ReportBlock::where('block_type', $blockType)->first();

                if ( ! $block) {
                    return [
                        'name'         => '',
                        'width'        => 'full',
                        'block_type'   => $blockType,
                        'data_source'  => '',
                        'default_band' => '',
                        'is_active'    => true,
                    ];
                }

                $data = $block->toArray();

                // Ensure all fields are present for entanglement
                $data['name'] ??= '';
                $data['block_type'] ??= $blockType;
                $data['data_source'] ??= '';
                $data['default_band'] ??= '';
                $data['is_active'] = (bool) ($data['is_active'] ?? true);

                // If it's a BackedEnum (width), we need to ensure it's the value
                if (isset($data['width']) && $data['width'] instanceof BackedEnum) {
                    $data['width'] = $data['width']->value;
                }
                $data['width'] ??= 'full';

                return $data;
            })
            ->mountUsing(function (Schema $schema, array $arguments) {
                $blockType = $arguments['blockType'] ?? null;
                if ( ! $blockType) {
                    return;
                }

                $block = ReportBlock::where('block_type', $blockType)->first();

                if ( ! $block) {
                    $schema->fill([
                        'name'         => '',
                        'width'        => 'full',
                        'block_type'   => $blockType,
                        'data_source'  => '',
                        'default_band' => '',
                        'is_active'    => true,
                    ]);

                    return;
                }

                $data = $block->toArray();

                // Ensure all fields are present for entanglement
                $data['name'] ??= '';
                $data['block_type'] ??= $blockType;
                $data['data_source'] ??= '';
                $data['default_band'] ??= '';
                $data['is_active'] = (bool) ($data['is_active'] ?? true);

                // If it's a BackedEnum (width), we need to ensure it's the value
                if (isset($data['width']) && $data['width'] instanceof BackedEnum) {
                    $data['width'] = $data['width']->value;
                }
                $data['width'] ??= 'full';

                $schema->fill($data);
            })
            ->action(function (array $data, array $arguments) {
                $blockType = $arguments['blockType'] ?? null;
                if ( ! $blockType) {
                    return;
                }
                $block = ReportBlock::where('block_type', $blockType)->first();
                if ($block) {
                    $block->update($data);
                    $this->dispatch('block-config-saved');
                }
            })
            ->modalWidth('4xl')
            ->slideOver();
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

    public function save($bands): void
    {
        // $bands is already grouped by band from Alpine.js
        $blocks = [];
        foreach ($bands as $band) {
            if ( ! isset($band['blocks'])) {
                continue;
            }
            foreach ($band['blocks'] as $block) {
                // Ensure the block data has all necessary fields before passing to service
                if ( ! isset($block['type'])) {
                    $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
                    $type         = str_replace('block_', '', $block['id']);
                    if (isset($systemBlocks[$type])) {
                        $block = BlockTransformer::toArray($systemBlocks[$type]);
                    }
                }

                $block['band']        = $band['key'] ?? 'header';
                $blocks[$block['id']] = $block;
            }
        }
        $this->blocks = $blocks;
        $service      = app(ReportTemplateService::class);
        $service->persistBlocks($this->record, $this->blocks);
        $this->dispatch('blocks-saved');
    }

    public function saveBlockConfiguration(string $blockType, array $config): void
    {
        $service = app(ReportTemplateService::class);
        $dbBlock = ReportBlock::where('block_type', $blockType)->first();

        if ($dbBlock) {
            $service->saveBlockConfig($dbBlock, $config);
            $this->dispatch('block-config-saved');
        }
    }

    public function getAvailableFields(): array
    {
        return [
            ['id' => 'company_name', 'label' => 'Company Name'],
            ['id' => 'company_address', 'label' => 'Company Address'],
            ['id' => 'company_phone', 'label' => 'Company Phone'],
            ['id' => 'company_email', 'label' => 'Company Email'],
            ['id' => 'company_vat_id', 'label' => 'Company VAT ID'],
            ['id' => 'client_name', 'label' => 'Client Name'],
            ['id' => 'client_address', 'label' => 'Client Address'],
            ['id' => 'client_phone', 'label' => 'Client Phone'],
            ['id' => 'client_email', 'label' => 'Client Email'],
            ['id' => 'invoice_number', 'label' => 'Invoice Number'],
            ['id' => 'invoice_date', 'label' => 'Invoice Date'],
            ['id' => 'invoice_due_date', 'label' => 'Due Date'],
            ['id' => 'invoice_subtotal', 'label' => 'Subtotal'],
            ['id' => 'invoice_tax_total', 'label' => 'Tax Total'],
            ['id' => 'invoice_total', 'label' => 'Invoice Total'],
            ['id' => 'item_description', 'label' => 'Item Description'],
            ['id' => 'item_quantity', 'label' => 'Item Quantity'],
            ['id' => 'item_price', 'label' => 'Item Price'],
            ['id' => 'item_tax_name', 'label' => 'Item Tax Name'],
            ['id' => 'item_tax_rate', 'label' => 'Item Tax Rate'],
            ['id' => 'footer_notes', 'label' => 'Notes'],
        ];
    }

    /**
     * Loads the template blocks from the filesystem via the service.
     */
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
