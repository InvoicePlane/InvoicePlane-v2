<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\Projects\Database\Factories\TaskFactory;

class Task extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    public const UPDATED_AT = 'task_finish_date';

    public $table = 'tasks';

    public $timestamps = false;

    public $filterable = [
        'project.project_name',
        'tax_rate.tax_rate_name',
        'task_name',
        'task_price',
        'task_finish_date',
    ];

    public $orderable = [
        'project.project_name',
        'tax_rate.tax_rate_name',
        'task_name',
        'task_price',
        'task_finish_date',
        'task_status',
    ];

    protected $primaryKey = 'task_id';

    protected $fillable = [
        'project_id',
        'task_name',
        'task_description',
        'task_price',
        'task_finish_date',
        'task_status',
        'tax_rate_id',
    ];

    protected $dates = [
        'task_finish_date',
        'deleted_at',
    ];

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    protected static function newFactory(): Factory
    {
        return TaskFactory::new();
    }
}
