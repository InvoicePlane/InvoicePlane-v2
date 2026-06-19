<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Client;
use Modules\Projects\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory;

    public $table = 'projects';

    public $timestamps = false;

    public $filterable = [
        'client.client_name',
        'client.client_active',
        'project_name',
    ];

    public $orderable = [
        'client.client_name',
        'client.client_active',
        'project_name',
    ];

    protected $primaryKey = 'project_id';

    protected $fillable = [
        'client_id',
        'project_name',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected static function newFactory(): Factory
    {
        return ProjectFactory::new();
    }
}
