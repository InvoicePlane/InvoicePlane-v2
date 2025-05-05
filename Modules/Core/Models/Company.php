<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\CompanyFactory;
use Modules\Projects\Models\Project;

/**
 * @property int             $id
 * @property string          $search_code
 * @property string          $slug
 * @property string          $name
 * @property string          $vat_number
 * @property string          $id_number
 * @property string          $coc_number
 * @property mixed           $created_at
 * @property mixed           $updated_at
 * @property CompanyUser[]   $companyUsers
 * @property DocumentGroup[] $documentGroups
 * @property Project[]       $projects
 * @property TaxRate[]       $taxRates
 */
class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['search_code', 'slug', 'name', 'vat_number', 'id_number', 'coc_number', 'created_at', 'updated_at'];

    public function addressables(): MorphMany
    {
        return $this->morphMany(Addressable::class, 'addressable');
    }

    public function addresses(): HasManyThrough
    {
        return $this->hasManyThrough(Address::class, Addressable::class, 'addressable_id', 'id', 'id', 'address_id');
    }

    public function companyUsers(): HasMany
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function documentGroups(): HasMany
    {
        return $this->hasMany(DocumentGroup::class);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }
}
