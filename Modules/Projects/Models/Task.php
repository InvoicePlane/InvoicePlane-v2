<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Projects\Database\Factories\TaskFactory;
use Modules\Projects\Enums\TaskStatus;

/**
 * @property int          $id
 * @property int          $company_id
 * @property int          $customer_id
 * @property int|null     $project_id
 * @property int|null     $tax_rate_id
 * @property int|null     $assigned_to
 * @property string|null  $task_number
 * @property string       $task_status
 * @property string|null  $task_name
 * @property float|null   $task_price
 * @property Carbon|null  $due_at
 * @property string|null  $description
 * @property User|null    $user
 * @property Company      $company
 * @property Relation     $relation
 * @property Project|null $project
 * @property TaxRate|null $tax_rate
 */
class Task extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'due_at'      => 'datetime',
        'task_status' => TaskStatus::class,
    ];

    protected $guarded = [];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'customer_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function relation(): BelongsTo
    {
        return $this->customer();
    }

    protected static function newFactory(): Factory
    {
        return TaskFactory::new();
    }
}
