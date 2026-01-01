<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Repositories\ReportTemplateFileRepository;
use Modules\Core\Transformers\BlockTransformer;
use Throwable;

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
     * @param Company                   $company      The company owning the template
     * @param string                    $name         The template name
     * @param string|ReportTemplateType $templateType The template type (e.g., 'invoice', 'quote')
     * @param array                     $blocks       Array of block data
     *
     * @return ReportTemplate The created template
     */
    public function createTemplate(
        Company $company,
        string $name,
        string|ReportTemplateType $templateType,
        array $blocks
    ): ReportTemplate {
        $this->validateBlocks($blocks);

        $template                = new ReportTemplate();
        $template->company_id    = $company->id;
        $template->name          = $name;
        $template->slug          = $this->makeUniqueSlug($company, $name);
        $template->template_type = is_string($templateType)
            ? ReportTemplateType::from($templateType)
            : $templateType;
        $template->is_system = false;
        $template->is_active = true;
        $template->save();

        try {
            $this->persistBlocks($template, $blocks);
        } catch (Throwable $e) {
            $template->delete();

            throw $e;
        }

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
     * @param string          $blockType The type of block to clone
     * @param string          $newId     The new block ID
     * @param GridPositionDTO $position  The new position
     *
     * @return BlockDTO The cloned block
     */
    public function cloneSystemBlock(
        string $blockType,
        string $newId,
        GridPositionDTO $position
    ): BlockDTO {
        $systemBlocks = $this->getSystemBlocks();

        if ( ! isset($systemBlocks[$blockType])) {
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
        $deleted = $this->fileRepository->delete(
            $template->company_id,
            $template->slug
        );

        if ( ! $deleted) {
            Log::warning('Failed to delete report template file', [
                'company_id' => $template->company_id,
                'slug'       => $template->slug,
            ]);
        }

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
        if ( ! is_array($blocks)) {
            throw new InvalidArgumentException('Blocks must be an array');
        }

        foreach ($blocks as $index => $block) {
            if ($block instanceof BlockDTO) {
                $block = BlockTransformer::toArray($block);
            }

            if ( ! is_array($block)) {
                throw new InvalidArgumentException("Block at index {$index} must be an array");
            }

            if ( ! isset($block['id']) || empty($block['id'])) {
                throw new InvalidArgumentException("Block at index {$index} must have an 'id'");
            }

            if ( ! isset($block['type']) || empty($block['type'])) {
                throw new InvalidArgumentException("Block at index {$index} must have a 'type'");
            }

            if ( ! isset($block['position']) || ! is_array($block['position'])) {
                throw new InvalidArgumentException("Block at index {$index} must have a 'position' array");
            }

            $position = $block['position'];
            if ( ! isset($position['x'], $position['y'], $position['width'], $position['height'])) {
                throw new InvalidArgumentException("Block at index {$index} position must have x, y, width, and height");
            }

            foreach (['x', 'y', 'width', 'height'] as $k) {
                if ( ! is_int($position[$k])) {
                    throw new InvalidArgumentException("Block at index {$index} position '{$k}' must be int");
                }
            }
            if ($position['width'] <= 0 || $position['height'] <= 0) {
                throw new InvalidArgumentException("Block at index {$index} position width/height must be > 0");
            }
            if ( ! array_key_exists('config', $block) || ! is_array($block['config'])) {
                throw new InvalidArgumentException("Block at index {$index} must have a 'config' array");
            }

            $positionDTO = GridPositionDTO::create(
                $position['x'],
                $position['y'],
                $position['width'],
                $position['height']
            );

            if ( ! $this->gridSnapper->validate($positionDTO)) {
                throw new InvalidArgumentException("Block at index {$index} has invalid position");
            }
        }
    }

    /**
     * Get system-defined blocks.
     *
     * @return array Array of system BlockDTO objects indexed by type
     */
    public function getSystemBlocks(): array
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
            'invoice',
            'details'
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
            'invoice',
            'details'
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
            'invoice',
            'footer'
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
            'invoice',
            'footer'
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
            'invoice',
            'footer'
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
        string $dataSource,
        string $band = 'header'
    ): BlockDTO {
        $position = GridPositionDTO::create($x, $y, $width, $height);

        $block = new BlockDTO();
        $block->setId($id)
            ->setType($type)
            ->setPosition($position)
            ->setConfig($config)
            ->setLabel($label)
            ->setIsCloneable(true)
            ->setDataSource($dataSource)
            ->setBand($band)
            ->setIsCloned(false)
            ->setClonedFrom(null);

        return $block;
    }

    /**
     * Generate a unique slug for the template within the company.
     *
     * @param Company $company The company
     * @param string  $name    The template name
     *
     * @return string The unique slug
     */
    private function makeUniqueSlug(Company $company, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (ReportTemplate::where('company_id', $company->id)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
