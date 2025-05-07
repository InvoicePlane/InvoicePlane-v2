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
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Projects\Models\Project;
use Modules\Quotes\Models\Quote;

//use Modules\Core\Events\CompanyProfileCreated;
//use Modules\Core\Events\CompanyProfileCreating;
//use Modules\Core\Events\CompanyProfileDeleted;
//use Modules\Core\Events\CompanyProfileSaving;

/**
 * @property int             $id
 * @property string          $search_code
 * @property string|null     $company_name
 * @property string          $slug
 * @property string          $vat_number
 * @property string          $id_number
 * @property string          $coc_number
 * @property string|null     $web
 * @property string|null     $logo
 * @property string          $quote_template
 * @property string          $invoice_template
 * @property CompanyUser[]   $companyUsers
 * @property DocumentGroup[] $documentGroups
 * @property Project[]       $projects
 * @property TaxRate[]       $taxRates
 */
class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Observer
    |--------------------------------------------------------------------------
    */
    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($companyProfile): void {
            event(new CompanyProfileSaving($companyProfile));
        });

        static::creating(function ($companyProfile): void {
            event(new CompanyProfileCreating($companyProfile));
        });

        static::created(function ($companyProfile): void {
            event(new CompanyProfileCreated($companyProfile));
        });

        static::deleted(function ($companyProfile): void {
            event(new CompanyProfileDeleted($companyProfile));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    public static function getList()
    {
        return self::orderBy('company_name')->pluck('company_name', 'id')->all();
    }

    public static function inUse($id)
    {
        if (Invoice::where('company_id', $id)->count()) {
            return true;
        }

        if (Quote::where('company_id', $id)->count()) {
            return true;
        }

        if (Expense::where('company_id', $id)->count()) {
            return true;
        }

        return (bool) (config('ip.defaultCompanyProfile') == $id);
    }

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

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\Invoice::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\Quote::class);
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    /**
     * Customers, Prospects, Relations.
     */
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

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFormattedAddressAttribute()
    {
        return nl2br(formatAddress($this));
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return route('companyProfiles.logo', [$this->id]);
        }
    }

    public function logo($width = null, $height = null)
    {
        if ($this->logo && file_exists(storage_path($this->logo))) {
            $logo = base64_encode(file_get_contents(storage_path($this->logo)));

            $style = '';

            if ($width && ! $height) {
                $style = 'width: ' . $width . 'px;';
            } elseif ($width && $height) {
                $style = 'width: ' . $width . 'px; height: ' . $height . 'px;';
            }

            return '<img id="cp-logo" src="data:image/png;base64,' . $logo . '" style="' . $style . '">';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }
}
