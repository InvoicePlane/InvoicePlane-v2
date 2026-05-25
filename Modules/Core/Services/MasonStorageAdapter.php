<?php

namespace Modules\Core\Services;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;

/**
 * Adapter to convert between Mason JSON format and InvoicePlane Block structure.
 *
 * Mason stores its editor state as JSON with a specific structure. This adapter
 * translates that format to/from our BlockDTO structure for filesystem persistence.
 */
class MasonStorageAdapter
{
    /**
     * Convert Mason JSON to Block DTOs for filesystem storage.
     *
     * @param string $masonJson Mason editor JSON state
     * @return array<string, BlockDTO> Array of BlockDTOs keyed by block ID
     */
    public function masonToBlocks(string $masonJson): array
    {
        $masonData = json_decode($masonJson, true);
        $blocks = [];

        if (!isset($masonData['content']) || !is_array($masonData['content'])) {
            return $blocks;
        }

        foreach ($masonData['content'] as $item) {
            if (($item['type'] ?? null) === 'masonBrick') {
                $attrs = $item['attrs'] ?? [];
                $block = $this->createBlockFromMasonBrick($attrs);
                
                if ($block) {
                    $blocks[$block->getId()] = $block;
                }
            }
        }

        return $blocks;
    }

    /**
     * Convert Block DTOs to Mason JSON for editor.
     *
     * @param array<BlockDTO> $blockDTOs Array of BlockDTOs
     * @return string Mason-compatible JSON
     */
    public function blocksToMason(array $blockDTOs): string
    {
        $content = [];

        foreach ($blockDTOs as $blockDTO) {
            $content[] = [
                'type' => 'masonBrick',
                'attrs' => [
                    'id' => $blockDTO->getId(),
                    'config' => $blockDTO->getConfig() ?? [],
                    'label' => $blockDTO->getLabel() ?? $this->getLabelForType($blockDTO->getType()),
                    'preview' => base64_encode($this->generatePreview($blockDTO)),
                ],
            ];
        }

        return json_encode([
            'type' => 'doc',
            'content' => $content,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Create BlockDTO from Mason brick attributes.
     *
     * @param array $attrs Mason brick attributes
     * @return BlockDTO|null
     */
    protected function createBlockFromMasonBrick(array $attrs): ?BlockDTO
    {
        $id = $attrs['id'] ?? null;
        $config = $attrs['config'] ?? [];
        $label = $attrs['label'] ?? '';

        if (!$id) {
            return null;
        }

        // Extract type from brick ID (e.g., "header_company_xyz123" -> "header_company")
        $type = $this->extractTypeFromId($id);

        // Create position DTO with defaults
        $position = GridPositionDTO::create(0, 0, 12, 4);

        $block = new BlockDTO();
        $block->setId($id)
            ->setType($type)
            ->setSlug(null)
            ->setPosition($position)
            ->setConfig($config)
            ->setLabel($label)
            ->setIsCloneable(false)
            ->setDataSource($this->getDataSourceForType($type))
            ->setIsCloned(false)
            ->setClonedFrom(null);

        return $block;
    }

    /**
     * Extract block type from Mason brick ID.
     *
     * @param string $brickId Mason brick ID (e.g., "header_company_abc123")
     * @return string Block type (e.g., "header_company")
     */
    protected function extractTypeFromId(string $brickId): string
    {
        // Remove trailing random suffix if present
        return preg_replace('/_[a-z0-9]+$/i', '', $brickId);
    }

    /**
     * Get human-readable label for a block type.
     *
     * @param string $type Block type
     * @return string Label
     */
    protected function getLabelForType(string $type): string
    {
        return match($type) {
            'header_company' => trans('ip.company_header'),
            'header_client' => trans('ip.client_header'),
            'header_invoice_meta' => trans('ip.invoice_metadata'),
            'detail_items' => trans('ip.line_items_table'),
            'footer_totals' => trans('ip.totals_section'),
            'footer_notes' => trans('ip.footer_notes'),
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Get data source for a block type.
     *
     * @param string $type Block type
     * @return string Data source
     */
    protected function getDataSourceForType(string $type): string
    {
        return match(true) {
            str_starts_with($type, 'header_company') => 'company',
            str_starts_with($type, 'header_client') => 'client',
            str_starts_with($type, 'header_invoice') => 'invoice',
            str_starts_with($type, 'detail_') => 'items',
            str_starts_with($type, 'footer_') => 'invoice',
            default => 'custom',
        };
    }

    /**
     * Generate preview HTML for a block (placeholder implementation).
     *
     * @param BlockDTO $block Block DTO
     * @return string Preview HTML
     */
    protected function generatePreview(BlockDTO $block): string
    {
        // This would render the appropriate preview view for the block type
        $type = $block->getType();
        $config = $block->getConfig() ?? [];

        // Simplified preview generation
        return sprintf(
            '<div class="block-preview"><strong>%s</strong></div>',
            htmlspecialchars($block->getLabel() ?? 'Block', ENT_QUOTES)
        );
    }
}
