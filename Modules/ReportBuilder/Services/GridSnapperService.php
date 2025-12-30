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
        $x      = max(0, min($position->getX(), $this->gridSize - 1));
        $y      = max(0, $position->getY());
        $width  = max(1, min($position->getWidth(), $this->gridSize - $position->getX()));
        $height = max(1, $position->getHeight());

        return GridPositionDTO::create($x, $y, $width, $height);
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

        return ! ($position->getX() + $position->getWidth() > $this->gridSize);
    }
}
