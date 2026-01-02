<?php

namespace Modules\Core\DTOs;

/**
 * Data Transfer Object for report builder blocks.
 *
 * Represents a single block in the report template layout with its configuration,
 * position, and metadata. Blocks can be system-provided or user-cloned.
 *
 * Example JSON:
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
 *     "font_size": 10
 *   },
 *   "label": "Company Header",
 *   "isCloneable": true,
 *   "dataSource": "company",
 *   "isCloned": false,
 *   "clonedFrom": null
 * }
 */
class BlockDTO
{
    //region Properties

    private string $id = '';

    private string $type = '';
    
    private ?string $slug = null;

    private ?GridPositionDTO $position = null;

    private array $config = [];

    private ?string $label = null;

    private bool $isCloneable = false;

    private ?string $dataSource = null;

    private string $band = 'header';

    private bool $isCloned = false;

    private ?string $clonedFrom = null;

    //endregion

    //region Static Factory Methods

    /**
     * Create a system block with default configuration.
     */
    public static function system(string $type, GridPositionDTO $position, array $config): self
    {
        $dto = new self();
        $dto->setType($type);
        $dto->setPosition($position);
        $dto->setConfig($config);
        $dto->setIsCloneable(true);
        $dto->setIsCloned(false);
        $dto->setClonedFrom(null);

        return $dto;
    }

    /**
     * Create a cloned block from an original block.
     */
    public static function clonedFrom(self $original, string $newId): self
    {
        $dto = new self();
        $dto->setId($newId);
        $dto->setType($original->getType());

        $originalPosition = $original->getPosition();
        $newPosition      = GridPositionDTO::create(
            $originalPosition->getX(),
            $originalPosition->getY(),
            $originalPosition->getWidth(),
            $originalPosition->getHeight()
        );

        $dto->setPosition($newPosition);
        $dto->setConfig($original->getConfig());
        $dto->setLabel($original->getLabel());
        $dto->setIsCloneable($original->getIsCloneable());
        $dto->setDataSource($original->getDataSource());
        $dto->setBand($original->getBand());
        $dto->setIsCloned(true);
        $dto->setClonedFrom($original->getId());

        return $dto;
    }

    //endregion

    //region Getters

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }
    
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getPosition(): ?GridPositionDTO
    {
        return $this->position;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getIsCloneable(): bool
    {
        return $this->isCloneable;
    }

    public function getDataSource(): ?string
    {
        return $this->dataSource;
    }

    public function getIsCloned(): bool
    {
        return $this->isCloned;
    }

    public function getClonedFrom(): ?string
    {
        return $this->clonedFrom;
    }

    //endregion

    //region Setters

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
    
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

        return $this;
    }

    public function setPosition(GridPositionDTO $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setIsCloneable(bool $isCloneable): self
    {
        $this->isCloneable = $isCloneable;

        return $this;
    }

    public function setDataSource(?string $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function setIsCloned(bool $isCloned): self
    {
        $this->isCloned = $isCloned;

        return $this;
    }

    public function setClonedFrom(?string $clonedFrom): self
    {
        $this->clonedFrom = $clonedFrom;

        return $this;
    }

    public function getBand(): string
    {
        return $this->band;
    }

    public function setBand(string $band): self
    {
        $this->band = $band;

        return $this;
    }

    //endregion
}
