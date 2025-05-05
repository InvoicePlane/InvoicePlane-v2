<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\TaxRateFactory;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;

/**
 * @property int     $id
 * @property int     $company_id
 * @property string  $type
 * @property mixed   $is_active
 * @property string  $name
 * @property string  $code
 * @property float   $rate
 * @property mixed   $created_at
 * @property mixed   $updated_at
 * @property Company $company
 */
class TaxRate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'rate'          => 'decimal:2',
        'is_active'     => 'boolean',
        'tax_rate_type' => TaxRateType::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tax_rate_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'tax_rate_id');
    }

    protected static function newFactory(): Factory
    {
        return TaxRateFactory::new();
    }
}
