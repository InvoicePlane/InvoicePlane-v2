<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\ReportBlockWidth;

class ReportBlock extends Model
{
    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'width'     => ReportBlockWidth::class,
        'config'    => 'array',
    ];
}
