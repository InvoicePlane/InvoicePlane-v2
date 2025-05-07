<?php

namespace Modules\Core\Models;

use Modules\Core\Database\Factories\DocumentGroupFactory;

use Modules\Invoices\Models\RecurringInvoice;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Enums\DocumentGroupType;

use Modules\Quotes\Models\Quote;

use Modules\Core\Models\Company;

use Modules\Core\Models\DocumentGroup;

use Modules\Core\Support\Results\Invoices;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Group.
 *
 * @property int                           $id
 * @property string|null                   $name
 * @property string                        $group_identifier_format
 * @property int                           $next_id
 * @property int                           $left_pad
 * @property string                        $format
 * @property int                           $reset_number
 * @property int                           $last_id
 * @property int                           $last_year
 * @property int                           $last_month
 * @property int                           $last_week
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
     * Return all of the “template–insertion” tags you want
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
        if ($group = self::where('name', $name)->first()) {
            return $group->id;
        }
    }

    public static function generateNumber($id): array|string
    {
        $group = self::find($id);

        // Only check for resets if this group has been used.
        if ($group->last_id != 0) {
            // Check for yearly reset.
            if ($group->reset_number == 1) {
                if ($group->last_year != date('Y')) {
                    $group->next_id = 1;
                    $group->save();
                }
            } // Check for monthly reset.
            elseif ($group->reset_number == 2) {
                if ($group->last_month != date('m') || $group->last_year != date('Y')) {
                    $group->next_id = 1;
                    $group->save();
                }
            } // Check for weekly reset.
            elseif ($group->reset_number == 3) {
                if ($group->last_week != date('W') || $group->last_month != date('m') || $group->last_year != date('Y')) {
                    $group->next_id = 1;
                    $group->save();
                }
            }
        }

        $number = $group->format;

        $number = str_replace('{NUMBER}', mb_str_pad($group->next_id, $group->left_pad, '0', STR_PAD_LEFT), $number);
        $number = str_replace('{YEAR}', date('Y'), $number);
        $number = str_replace('{MONTH}', date('m'), $number);
        $number = str_replace('{WEEK}', date('W'), $number);
        $number = str_replace('{MONTHSHORTNAME}', date('M'), $number);

        $group->last_id    = $group->next_id;
        $group->last_week  = date('W');
        $group->last_month = date('m');
        $group->last_year  = date('Y');
        $group->save();

        return $number;
    }

    public static function getList()
    {
        return self::orderBy('name')->pluck('name', 'id')->all();
    }

    public static function incrementNextId($document): void
    {
        $group          = self::find($document->group_id);
        $group->last_id = $group->next_id;  // Setting last_id to old nex_id before increment
        $group->next_id = $group->next_id + 1;
        $group->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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
    | Relationships
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return DocumentGroupFactory::new();
    }
}
