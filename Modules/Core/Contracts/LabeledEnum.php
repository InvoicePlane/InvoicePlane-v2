<?php

namespace Modules\Core\Contracts;

interface LabeledEnum
{
    public function label(): string;

    public function color(): string;
}
