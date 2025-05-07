<?php

namespace Modules\Projects\Models;

use Modules\Projects\Models\Task;

use Modules\Projects\Enums\TaskStatus;

use Modules\Projects\Database\Factories\TaskFactory;

use Modules\Core\Models\TaxRate;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Projects\Models\Project;

use Modules\Clients\Models\Relation;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Projects\Database\Factories\TaskFactory;
use Modules\Projects\Enums\TaskStatus;

/**
 * @property int      $id
 * @property int      $customer_id
 * @property int      $project_id
 * @property int      $assigned_to
 * @property string   $task_status
 * @property string   $task_name
 * @property string   $task_due_at
 * @property string   $task_description
 * @property mixed    $created_at
 * @property mixed    $updated_at
 * @property User     $user
 * @property Project  $project
 * @property Relation $customer
 */
class Task extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'task_due_at' => 'date',
        'task_status' => TaskStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    protected static function newFactory(): Factory
    {
        return TaskFactory::new();
    }
}
