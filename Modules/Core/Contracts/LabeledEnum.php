<?php

namespace Modules\Core\Contracts;

use Modules\Core\Contracts\LabeledEnum;

interface LabeledEnum
{
    public function label(): string;

    public function color(): string;
}
