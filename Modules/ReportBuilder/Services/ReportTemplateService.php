<?php

namespace Modules\ReportBuilder\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Models\Company;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Repositories\ReportTemplateFileRepository;
use Modules\ReportBuilder\Transformers\BlockTransformer;

/**
 * Service for managing report templates and their blocks.
 *
 * Handles creation, updating, cloning, and persistence of report templates
 * and their associated block configurations.
 *
 * Block JSON Structure:
 * {
 *   "id": "block_header_company",
 *   "type": "header_company",
 *   "position": {"x": 0, "y": 0, "width": 6, "height": 4},
 *   "config": {
 *     "show_vat_id": true,
 *     "show_phone": true,
 *     "font_size": 10
 *   },
 *   "label": "Company Header",
 *   "isCloneable": true,
 *   "dataSource": "company",
 *   "isCloned": false,
 *   "clonedFrom": null
 * }
 */
class ReportTemplateService
{
    private ReportTemplateFileRepository $fileRepository;

    private GridSnapperService $gridSnapper;

    public function __construct(
        ReportTemplateFileRepository $fileRepository,
        GridSnapperService $gridSnapper
    ) {
        $this->fileRepository = $fileRepository;
        $this->gridSnapper    = $gridSnapper;
    }

    /**
     * Create a new report template.
     *
     * @param Company $company      The company owning the template
     * @param string  $name         The template name
     * @param string  $templateType The template type (e.g., 'invoice', 'quote')
     * @param array   $blocks       Array of block data
     *
     * @return ReportTemplate The created template
     */
    public function createTemplate(
        Company $company,
        string $name,
        string $templateType,
        array $blocks
    ): ReportTemplate {
        $this->validateBlocks($blocks);

        $template = new ReportTemplate();
        $template->company_id    = $company->id;
        $template->name          = $name;
        $template->slug          = Str::slug($name);
        $template->template_type = $templateType;
        $template->is_system     = false;
        $template->is_active     = true;
        $template->save();

        $this->persistBlocks($template, $blocks);

        return $template;
    }

    /**
     * Update an existing report template.
     *
     * @param ReportTemplate $template The template to update
     * @param array          $blocks   Array of block data
     *
     * @return ReportTemplate The updated template
     */
    public function updateTemplate(ReportTemplate $template, array $blocks): ReportTemplate
    {
        $this->validateBlocks($blocks);
        $this->persistBlocks($template, $blocks);

        return $template;
    }

    /**
     * Clone a system block with a new ID and position.
     *
     * @param string           $blockType The type of block to clone
     * @param string           $newId     The new block ID
     * @param GridPositionDTO  $position  The new position
     *
     * @return BlockDTO The cloned block
     */
    public function cloneSystemBlock(
        string $blockType,
        string $newId,
        GridPositionDTO $position
    ): BlockDTO {
        $systemBlocks = $this->getSystemBlocks();

        if (!isset($systemBlocks[$blockType])) {
            throw new InvalidArgumentException("System block type '{$blockType}' not found");
        }

        $originalBlock = $systemBlocks[$blockType];
        $cloned        = BlockDTO::clonedFrom($originalBlock, $newId);
        $cloned->setPosition($position);

        return $cloned;
    }

    /**
     * Persist blocks to filesystem via repository.
     *
     * @param ReportTemplate $template The template to persist blocks for
     * @param array          $blocks   Array of block data or BlockDTO objects
     *
     * @return void
     */
    public function persistBlocks(ReportTemplate $template, array $blocks): void
    {
        $blocksArray = [];

        foreach ($blocks as $block) {
            if ($block instanceof BlockDTO) {
                $blocksArray[] = BlockTransformer::toArray($block);
            } else {
                $blocksArray[] = $block;
            }
        }

        $this->fileRepository->save(
            $template->company_id,
            $template->slug,
            $blocksArray
        );
    }

    /**
     * Load blocks from filesystem via repository.
     *
     * @param ReportTemplate $template The template to load blocks for
     *
     * @return array Array of BlockDTO objects
     */
    public function loadBlocks(ReportTemplate $template): array
    {
        $blocksData = $this->fileRepository->get(
            $template->company_id,
            $template->slug
        );

        return BlockTransformer::toArrayCollection($blocksData);
    }

    /**
     * Delete a report template.
     *
     * @param ReportTemplate $template The template to delete
     *
     * @return void
     */
    public function deleteTemplate(ReportTemplate $template): void
    {
        $this->fileRepository->delete(
            $template->company_id,
            $template->slug
        );

        $template->delete();
    }

    /**
     * Validate an array of blocks.
     *
     * @param array $blocks Array of block data
     *
     * @return void
     *
     * @throws InvalidArgumentException If blocks are invalid
     */
    public function validateBlocks(array $blocks): void
    {
        if (!is_array($blocks)) {
            throw new InvalidArgumentException('Blocks must be an array');
        }

        foreach ($blocks as $index => $block) {
            if ($block instanceof BlockDTO) {
                $block = BlockTransformer::toArray($block);
            }

            if (!is_array($block)) {
                throw new InvalidArgumentException("Block at index {$index} must be an array");
            }

            if (!isset($block['id']) || empty($block['id'])) {
                throw new InvalidArgumentException("Block at index {$index} must have an 'id'");
            }

            if (!isset($block['type']) || empty($block['type'])) {
                throw new InvalidArgumentException("Block at index {$index} must have a 'type'");
            }

            if (!isset($block['position']) || !is_array($block['position'])) {
                throw new InvalidArgumentException("Block at index {$index} must have a 'position' array");
            }

            $position = $block['position'];
            if (!isset($position['x'], $position['y'], $position['width'], $position['height'])) {
                throw new InvalidArgumentException("Block at index {$index} position must have x, y, width, and height");
            }

            $positionDTO = new GridPositionDTO();
            $positionDTO->setX($position['x'])
                ->setY($position['y'])
                ->setWidth($position['width'])
                ->setHeight($position['height']);

            if (!$this->gridSnapper->validate($positionDTO)) {
                throw new InvalidArgumentException("Block at index {$index} has invalid position");
            }
        }
    }

    /**
     * Get system-defined blocks.
     *
     * @return array Array of system BlockDTO objects indexed by type
     */
    private function getSystemBlocks(): array
    {
        $blocks = [];

        $blocks['header_company'] = $this->createSystemBlock(
            'block_header_company',
            'header_company',
            0,
            0,
            6,
            4,
            ['show_vat_id' => true, 'show_phone' => true, 'font_size' => 10],
            'Company Header',
            'company'
        );

        $blocks['header_client'] = $this->createSystemBlock(
            'block_header_client',
            'header_client',
            6,
            0,
            6,
            4,
            ['show_address' => true, 'show_phone' => true, 'font_size' => 10],
            'Client Header',
            'client'
        );

        $blocks['header_invoice_meta'] = $this->createSystemBlock(
            'block_header_invoice_meta',
            'header_invoice_meta',
            0,
            4,
            12,
            2,
            ['show_date' => true, 'show_due_date' => true, 'show_number' => true],
            'Invoice Metadata',
            'invoice'
        );

        $blocks['detail_items'] = $this->createSystemBlock(
            'block_detail_items',
            'detail_items',
            0,
            6,
            12,
            6,
            ['show_description' => true, 'show_quantity' => true, 'show_price' => true],
            'Invoice Items',
            'invoice'
        );

        $blocks['detail_item_tax'] = $this->createSystemBlock(
            'block_detail_item_tax',
            'detail_item_tax',
            0,
            12,
            12,
            2,
            ['show_tax_name' => true, 'show_tax_rate' => true],
            'Item Tax Details',
            'invoice'
        );

        $blocks['footer_totals'] = $this->createSystemBlock(
            'block_footer_totals',
            'footer_totals',
            6,
            14,
            6,
            4,
            ['show_subtotal' => true, 'show_tax' => true, 'show_total' => true],
            'Invoice Totals',
            'invoice'
        );

        $blocks['footer_notes'] = $this->createSystemBlock(
            'block_footer_notes',
            'footer_notes',
            0,
            14,
            6,
            4,
            ['font_size' => 9],
            'Footer Notes',
            'invoice'
        );

        $blocks['footer_qr_code'] = $this->createSystemBlock(
            'block_footer_qr_code',
            'footer_qr_code',
            0,
            18,
            4,
            4,
            ['size' => 100],
            'QR Code',
            'invoice'
        );

        return $blocks;
    }

    /**
     * Create a system block.
     */
    private function createSystemBlock(
        string $id,
        string $type,
        int $x,
        int $y,
        int $width,
        int $height,
        array $config,
        string $label,
        string $dataSource
    ): BlockDTO {
        $position = new GridPositionDTO();
        $position->setX($x)->setY($y)->setWidth($width)->setHeight($height);

        $block = new BlockDTO();
        $block->setId($id)
            ->setType($type)
            ->setPosition($position)
            ->setConfig($config)
            ->setLabel($label)
            ->setIsCloneable(true)
            ->setDataSource($dataSource)
            ->setIsCloned(false)
            ->setClonedFrom(null);

        return $block;
    }
}
