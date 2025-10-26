<?php

namespace Modules\ReportBuilder\Services;

use Modules\ReportBuilder\DTOs\GridPositionDTO;

/**
 * Service for snapping grid positions to grid boundaries.
 *
 * Ensures that block positions are aligned to the grid system
 * to maintain consistent layout in the report builder.
 */
class GridSnapperService
{
    private int $gridSize;

    public function __construct(int $gridSize = 12)
    {
        $this->gridSize = $gridSize;
    }

    /**
     * Snap a position to the grid.
     */
    public function snap(GridPositionDTO $position): GridPositionDTO
    {
        $snapped = new GridPositionDTO();
        $snapped->setX(max(0, min($position->getX(), $this->gridSize - 1)));
        $snapped->setY(max(0, $position->getY()));
        $snapped->setWidth(max(1, min($position->getWidth(), $this->gridSize - $position->getX())));
        $snapped->setHeight(max(1, $position->getHeight()));

        return $snapped;
    }

    /**
     * Validate that a position fits within the grid.
     */
    public function validate(GridPositionDTO $position): bool
    {
        if ($position->getX() < 0 || $position->getX() >= $this->gridSize) {
            return false;
        }

        if ($position->getY() < 0) {
            return false;
        }

        if ($position->getWidth() < 1) {
            return false;
        }

        if ($position->getHeight() < 1) {
            return false;
        }

        if ($position->getX() + $position->getWidth() > $this->gridSize) {
            return false;
        }

        return true;
    }
}
