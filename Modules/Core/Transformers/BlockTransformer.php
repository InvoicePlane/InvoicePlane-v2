<?php

namespace Modules\Core\Transformers;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;

/**
 * Transformer for converting BlockDTO to and from various formats.
 *
 * Handles transformation between BlockDTO objects and array/JSON representations
 * for storage and API responses.
 *
 * Full JSON Block Structure Example:
 * {
 *   "id": "block_company_header",
 *   "type": "company_header",
 *   "position": {
 *     "x": 0,
 *     "y": 0,
 *     "width": 6,
 *     "height": 4
 *   },
 *   "config": {
 *     "show_vat_id": true,
 *     "show_phone": true,
 *     "show_email": true,
 *     "show_address": true,
 *     "font_size": 10,
 *     "font_weight": "bold",
 *     "text_align": "left"
 *   },
 *   "label": "Company Header",
 *   "isCloneable": true,
 *   "dataSource": "company",
 *   "isCloned": false,
 *   "clonedFrom": null
 * }
 */
class BlockTransformer
{
    /**
     * Convert array data to BlockDTO.
     */
    public static function toDTO(array $blockData): BlockDTO
    {
        $positionData = $blockData['position'] ?? [];
        $position     = GridPositionDTO::create(
            $positionData['x'] ?? 0,
            $positionData['y'] ?? 0,
            $positionData['width'] ?? 1,
            $positionData['height'] ?? 1
        );

        $dto = new BlockDTO();
        $dto->setId($blockData['id'] ?? '')
            ->setType($blockData['type'] ?? '')
            ->setPosition($position)
            ->setConfig($blockData['config'] ?? [])
            ->setLabel($blockData['label'] ?? null)
            ->setIsCloneable($blockData['isCloneable'] ?? false)
            ->setDataSource($blockData['dataSource'] ?? null)
            ->setBand($blockData['band'] ?? 'header')
            ->setIsCloned($blockData['isCloned'] ?? false)
            ->setClonedFrom($blockData['clonedFrom'] ?? null);

        return $dto;
    }

    /**
     * Convert BlockDTO to array.
     */
    public static function toArray(BlockDTO $dto): array
    {
        $position = $dto->getPosition();

        return [
            'id'       => $dto->getId(),
            'type'     => $dto->getType(),
            'position' => $position ? [
                'x'      => $position->getX(),
                'y'      => $position->getY(),
                'width'  => $position->getWidth(),
                'height' => $position->getHeight(),
            ] : null,
            'config'      => $dto->getConfig(),
            'label'       => $dto->getLabel(),
            'isCloneable' => $dto->getIsCloneable(),
            'dataSource'  => $dto->getDataSource(),
            'band'        => $dto->getBand(),
            'isCloned'    => $dto->getIsCloned(),
            'clonedFrom'  => $dto->getClonedFrom(),
        ];
    }

    /**
     * Convert BlockDTO to JSON string.
     */
    public static function toJson(BlockDTO $dto, bool $pretty = true): string
    {
        $array = self::toArray($dto);
        $flags = JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($array, $flags);
    }

    /**
     * Convert array of block data to array of BlockDTOs.
     */
    public static function toArrayCollection(array $blocks): array
    {
        return array_map(fn ($blockData) => self::toDTO($blockData), $blocks);
    }
}
