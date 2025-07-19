<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\DocumentGroupFactory;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Quotes\Models\Quote;

/**
 * @property int                           $id
 * @property int                           $company_id
 * @property string                        $type
 * @property string                        $name
 * @property string                        $group_identifier_format
 * @property int                           $next_id
 * @property int                           $left_pad
 * @property string|null                   $format
 * @property int                           $reset_number
 * @property int                           $last_id
 * @property int                           $last_year
 * @property int                           $last_month
 * @property int                           $last_week
 * @property Company                       $company
 * @property Collection|Invoice[]          $invoices
 * @property Collection|Quote[]            $quotes
 * @property Collection|RecurringInvoice[] $recurring_invoices
 */
class DocumentGroup extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'type' => DocumentGroupType::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Return all the “template–insertion” tags you want
     * to offer in your form dropdown.
     */
    public static function availableTags(): array
    {
        return [
            '{{yy}}'    => trans('ip.year_short'),    // e.g. “23”
            '{{year}}'  => trans('ip.year_full'),     // e.g. “2023”
            '{{month}}' => trans('ip.month'),         // e.g. “04”
            '{{day}}'   => trans('ip.day'),           // e.g. “27”
            '{{id}}'    => trans('ip.id'),            // e.g. “42”
        ];
    }

    public static function findIdByName($name)
    {
        if ($group = self::query()->where('name', $name)->first()) {
            return $group->id;
        }
    }

    public static function getList()
    {
        return self::orderBy('name')->pluck('name', 'id')->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'document_group_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'document_group_id');
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Factories
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return DocumentGroupFactory::new();
    }
}
