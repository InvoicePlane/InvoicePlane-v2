<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Invoices\Database\Factories\InvoiceGroupFactory;
use Modules\Quotes\Models\Quote;

class InvoiceGroup extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $table = 'invoice_groups';

    public $timestamps = false;

    public $filterable = [
        'invoice_group_name',
    ];

    public $orderable = [
        'invoice_group_name',
    ];

    protected $fillable = [
        'invoice_group_name',
        'invoice_group_identifier_format',
        'invoice_group_next_id',
        'invoice_group_left_pad',
    ];

    protected $primaryKey = 'invoice_group_id';

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoice_group_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'invoice_group_id');
    }

    protected static function newFactory(): Factory
    {
        return InvoiceGroupFactory::new();
    }
}
