<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Quotes\Database\Factories\QuoteAmountFactory;

class QuoteAmount extends Model
{
    use HasFactory;

    public $table = 'quote_amounts';

    public $timestamps = false;

    protected $primaryKey = 'quote_amount_id';

    protected $fillable = [
        'quote_id',
        'quote_item_subtotal',
        'quote_item_tax_total',
        'quote_tax_total',
        'quote_total',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    protected static function newFactory(): Factory
    {
        return QuoteAmountFactory::new();
    }
}
