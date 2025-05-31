<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Database\Factories\ContactFactory;
use Modules\Core\Enums\CommunicationType;
use Modules\Core\Enums\Gender;
use Modules\Core\Models\Address;
use Modules\Core\Models\Addressable;
use Modules\Core\Models\Communication;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int        $id
 * @property string     $contact_first_name
 * @property string     $contact_last_name
 * @property bool       $default_to
 * @property bool       $default_cc
 * @property bool       $default_bcc
 * @property mixed      $gender
 * @property Relation[] $relations
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
    public function getFormattedContactAttribute()
    {
        return $this->name . ' <' . $this->email . '>';
    }

    public function getFormattedDefaultBccAttribute()
    {
        return ($this->default_bcc) ? trans('ip.yes') : trans('ip.no');
    }

    public function getFormattedDefaultCcAttribute()
    {
        return ($this->default_cc) ? trans('ip.yes') : trans('ip.no');
    }

    public function getFormattedDefaultToAttribute()
    {
        return ($this->default_to) ? trans('ip.yes') : trans('ip.no');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->communications
            ->where('contactable_type', CommunicationType::EMAIL->value)
            ->where('is_primary', true)
            ->first()?->contactable_value;
    }

    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->communications
            ->where('contactable_type', CommunicationType::PHONE->value)
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
