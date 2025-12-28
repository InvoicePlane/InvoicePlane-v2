<?php

namespace Modules\Core\DataTransferObjects;

use Modules\Core\Models\Numbering;

class NumberingUpdateResult
{
    public function __construct(
        public Numbering $numbering,
        public bool $nextIdAdjusted,
        public int $originalNextId
    ) {}
}
