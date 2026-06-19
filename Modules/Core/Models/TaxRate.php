<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\TaxRateFactory;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;

class TaxRate extends Model
{
    use HasFactory;

    public $table = 'tax_rates';

    public $timestamps = false;

    public $guarded = [];

    public $filterable = [
        'tax_rate_name',
        'tax_rate_percent',
    ];

    public $orderable = [
        'tax_rate_name',
        'tax_rate_percent',
    ];

    protected $primaryKey = 'tax_rate_id';

    protected $fillable = [
        'tax_rate_name',
        'tax_rate_percent',
    ];

    protected $casts = [
        'tax_rate_id'      => 'integer',
        'tax_rate_name'    => 'string',
        'tax_rate_percent' => 'decimal:2',
    ];

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
        //TODO: move the model or the factory
        return TaxRateFactory::new();
    }
}
