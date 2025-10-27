<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Database\Factories\ContactFactory;
use Modules\Clients\Enums\Gender;
use Modules\Core\Enums\CommunicationType;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                   $id
 * @property int                   $company_id
 * @property int                   $relation_id
 * @property string                $first_name
 * @property string                $last_name
 * @property bool|null             $default_to
 * @property bool|null             $default_cc
 * @property bool|null             $default_bcc
 * @property string|null           $gender
 * @property Company               $company
 * @property Relation              $relation
 * @property Collection|Relation[] $relations
 */
class Contact extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'gender' => Gender::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    /**
     * Get all of the contact's addresses.
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the contact's primary address.
     */
    public function primaryAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('is_primary', true);
    }

    /**
     * Get the contact's home address.
     */
    public function homeAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'home');
    }

    /**
     * Get the contact's work address.
     */
    public function workAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'work');
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicationable');
    }

    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class, 'primary_contact_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFullNameAttribute(): string
    {
        return mb_trim($this->first_name . ' ' . $this->last_name);
    }

    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->communications
            ->where('communication_type', CommunicationType::EMAIL->value)
            ->where('is_primary', true)
            ->first()?->contactable_value;
    }

    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->communications
            ->where('communication_type', CommunicationType::PHONE->value)
            ->where('is_primary', true)
            ->first()?->contactable_value;
    }

    public function getCompanyNameAttribute()
    {
        return $this->company_id ? Company::query()->find($this->company_id)->company_name : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ContactFactory::new();
    }
}
