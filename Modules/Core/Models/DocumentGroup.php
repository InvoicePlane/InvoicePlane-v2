<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\DocumentGroupFactory;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

/**
 * @property int     $id
 * @property int     $company_id
 * @property string  $name
 * @property string  $prefix
 * @property string  $format
 * @property mixed   $next_id
 * @property mixed   $created_at
 * @property mixed   $updated_at
 * @property Company $company
 */
class DocumentGroup extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => DocumentGroupType::class,
    ];

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

    protected static function newFactory(): Factory
    {
        return DocumentGroupFactory::new();
    }
}
