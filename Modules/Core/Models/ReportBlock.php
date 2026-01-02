<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportDataSource;

class ReportBlock extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'width'     => ReportBlockWidth::class,
        'data_source' => ReportDataSource::class,
        'default_band' => ReportBand::class,
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\ReportBlockFactory
    {
        return \Modules\Core\Database\Factories\ReportBlockFactory::new();
    }
}
