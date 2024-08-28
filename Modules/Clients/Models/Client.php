<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Database\Factories\ClientFactory;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Quotes\Models\Quote;

class Client extends Model
{
    use HasFactory;

    public const CREATED_AT = 'client_date_created';

    public const UPDATED_AT = 'client_date_modified';

    public $table = 'clients';

    public $timestamps = true;

    public $filterable = [
        'client_name',
    ];

    public $orderable = [
        'client_name',
        'client_active',
    ];

    public static $rules = [
        'client_name'          => 'required|string',
        'client_address_1'     => 'nullable|string',
        'client_address_2'     => 'nullable|string',
        'client_city'          => 'nullable|string',
        'client_state'         => 'nullable|string',
        'client_zip'           => 'nullable|string',
        'client_country'       => 'nullable|string',
        'client_phone'         => 'nullable|string',
        'client_fax'           => 'nullable|string',
        'client_mobile'        => 'nullable|string',
        'client_email'         => 'nullable|email',
        'client_web'           => 'nullable|URL',
        'client_vat_id'        => 'nullable|string',
        'client_tax_code'      => 'nullable|string',
        'client_language'      => 'nullable|string',
        'client_active'        => 'nullable|boolean',
        'client_surname'       => 'nullable|string',
        'client_avs'           => 'nullable|string',
        'client_insurednumber' => 'nullable|string',
        'client_veka'          => 'nullable|string',
        'client_birthdate'     => 'nullable|date:Y-m-d',
        'client_gender'        => 'nullable|boolean', //TODO: does this field exist?
    ];

    protected $fillable = [
        'client_name',
        'client_address_1',
        'client_address_2',
        'client_city',
        'client_state',
        'client_zip',
        'client_country',
        'client_phone',
        'client_fax',
        'client_mobile',
        'client_email',
        'client_web',
        'client_vat_id',
        'client_tax_code',
        'client_language',
        'client_active',
        'client_surname',
        'client_avs',
        'client_insurednumber',
        'client_veka',
        'client_birthdate',
        'client_gender',
    ];

    protected $primaryKey = 'client_id';

    protected $dates = [
        'client_date_created',
        'client_date_modified',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'client_id');
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        switch ($status) {
            case 'active':
                return $query->where('client_active', true);
            case 'inactive':
                return $query->where('client_active', false);
            default:
                return $query;
        }
    }

    protected static function newFactory(): Factory
    {
        return ClientFactory::new();
    }
}
