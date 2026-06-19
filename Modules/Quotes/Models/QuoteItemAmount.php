<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Quotes\Database\Factories\QuoteItemAmountFactory;

class QuoteItemAmount extends Model
{
    use HasFactory;

    public $table = 'quote_item_amounts';

    public $timestamps = false;

    public $fillable = [
        'item_id',
        'item_subtotal',
        'item_tax_total',
        'item_discount',
        'item_total',
    ];

    protected $primaryKey = 'item_amount_id';

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class, 'item_id');
    }

    protected static function newFactory(): Factory
    {
        return QuoteItemAmountFactory::new();
    }
}
