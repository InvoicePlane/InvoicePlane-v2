<?php

namespace Modules\ReportBuilder\DTOs;

/**
 * Data Transfer Object for grid position coordinates.
 *
 * Represents a position in the report layout grid system.
 *
 * Example JSON:
 * {
 *   "x": 0,
 *   "y": 0,
 *   "width": 6,
 *   "height": 4
 * }
 */
class GridPositionDTO
{
    //region Properties

    private int $x;

    private int $y;

    private int $width;

    private int $height;

    //endregion

    //region Constructor

    public function __construct(int $x, int $y, int $width, int $height)
    {
        if ($x < 0 || $y < 0) {
            throw new \InvalidArgumentException('x and y must be >= 0');
        }
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('width and height must be > 0');
        }
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    //endregion

    //region Getters

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    //endregion

    //region Setters

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    //endregion
}
