<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportBlock;
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
 *   "id": "block_company_header",
 *   "type": "company_header",
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
        $groupedBlocks = [
            'header'       => [],
            'group_header' => [],
            'details'      => [],
            'group_footer' => [],
            'footer'       => [],
        ];

        foreach ($blocks as $block) {
            $blockArray = $block instanceof BlockDTO ? BlockTransformer::toArray($block) : $block;
            $band       = $blockArray['band'] ?? 'header';

            if (isset($groupedBlocks[$band])) {
                $groupedBlocks[$band][] = $blockArray;
            } else {
                $groupedBlocks['header'][] = $blockArray;
            }
        }

        $this->fileRepository->save(
            $template->company_id,
            $template->slug,
            $groupedBlocks
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
     * Get system-defined blocks from database.
     *
     * @return array array of BlockDTO objects indexed by type
     */
    public function getSystemBlocks(): array
    {
        $blocks   = [];
        $dbBlocks = ReportBlock::where('is_active', true)->get();

        foreach ($dbBlocks as $dbBlock) {
            $config = $this->getBlockConfig($dbBlock);

            // Map widths to grid units for the designer using the enum method
            $width = $dbBlock->width->getGridWidth();

            $blocks[$dbBlock->block_type] = $this->createSystemBlock(
                'block_' . $dbBlock->block_type,
                $dbBlock->block_type,
                $dbBlock->slug,
                0,
                0,
                $width,
                4,
                $config,
                $dbBlock->name,
                $dbBlock->data_source->value,
                $dbBlock->default_band->value
            );
        }

        return $blocks;
    }

    /**
     * Get block configuration from JSON file.
     *
     * @param ReportBlock $block
     *
     * @return array
     */
    public function getBlockConfig(ReportBlock $block): array
    {
        return $block->config ?: [];
    }

    /**
     * Save block configuration to database.
     *
     * @param ReportBlock $block
     * @param array       $config
     *
     * @return void
     */
    public function saveBlockConfig(ReportBlock $block, array $config): void
    {
        $block->config = $config;
        $block->save();
    }

    /**
     * Create a system block.
     * bands:
     * $bands = [
     * 'header' => 'Header',
     * 'group_header' => 'Group Detail Header',
     * 'details' => 'Details',
     * 'group_footer' => 'Group Detail Footer',
     * 'footer' => 'Footer',
     * ];.
     */
    private function createSystemBlock(
        string $id,
        string $type,
        ?string $slug,
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
            ->setSlug($slug)
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

        while (ReportTemplate::query()->where('company_id', $company->id)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
