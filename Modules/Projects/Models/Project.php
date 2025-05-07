<?php

namespace Modules\Projects\Models;

use Modules\Projects\Models\Task;

use Modules\Projects\Enums\ProjectStatus;

use Modules\Projects\Database\Factories\ProjectFactory;

use Modules\Core\Support\Results\Clients;

use Modules\Projects\Models\Project;

use Modules\Core\Models\Company;

use Modules\Clients\Models\Relation;

use Modules\Core\Traits\BelongsToCompany;

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
 * @property int         $id
 * @property int         $company_id
 * @property int         $customer_id
 * @property string      $project_status
 * @property string      $name
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Company     $company
 * @property Relation    $customer
 * @property Task[]      $tasks
 */
class Project extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'start_at'       => 'date',
        'end_at'         => 'date',
        'project_status' => ProjectStatus::class,
    ];

    //
    // Relationships (alphabetical)
    //

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return ProjectFactory::new();
    }
}
