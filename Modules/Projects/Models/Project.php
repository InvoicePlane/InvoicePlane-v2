<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Projects\Database\Factories\ProjectFactory;
use Modules\Projects\Enums\ProjectStatus;

/**
 * @property int               $id
 * @property int               $company_id
 * @property int               $customer_id
 * @property string            $project_status
 * @property string|null       $project_name
 * @property Carbon|null       $start_at
 * @property Carbon|null       $end_at
 * @property string|null       $description
 * @property Company           $company
 * @property Relation          $relation
 * @property Collection|Task[] $tasks
 */
class Project extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'project_status' => ProjectStatus::class,
        'start_at'       => 'date',
        'end_at'         => 'date',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'customer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function relation(): BelongsTo
    {
        return $this->customer();
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ProjectFactory::new();
    }
}
