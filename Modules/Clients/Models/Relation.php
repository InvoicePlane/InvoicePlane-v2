<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Database\Factories\RelationFactory;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Core\Models\Address;
use Modules\Core\Models\Addressable;
use Modules\Core\Models\Communication;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

/**
 * @property int       $id
 * @property int       $primary_contact_id
 * @property string    $relation_type
 * @property string    $relation_status
 * @property string    $relation_number
 * @property string    $company_name
 * @property string    $trading_name
 * @property string    $id_number
 * @property string    $coc_number
 * @property string    $vat_number
 * @property Carbon    $registered_at
 * @property mixed     $created_at
 * @property mixed     $updated_at
 * @property Invoice[] $invoices
 * @property Quote[]   $quotes
 * @property Project[] $projects
 * @property Contact   $contact
 * @property Task[]    $tasks
 */
class Relation extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $table = 'relations';

    protected $guarded = [];

    protected $casts = [
        'relation_type'   => RelationType::class,
        'relation_status' => RelationStatus::class,
    ];

    public function addressables(): MorphMany
    {
        return $this->morphMany(Addressable::class, 'addressable');
    }

    public function addresses(): HasManyThrough
    {
        return $this->hasManyThrough(
            Address::class,
            Addressable::class,
            'addressable_id',
            'id',
            'id',
            'address_id'
        );
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicationable');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'relation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected static function newFactory(): Factory
    {
        return RelationFactory::new();
    }
}
