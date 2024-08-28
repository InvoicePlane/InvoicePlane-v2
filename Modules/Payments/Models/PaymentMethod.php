<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Payments\Database\Factories\PaymentMethodFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    public $table = 'payment_methods';

    public $timestamps = false;

    public $filterable = [
        'payment_method_name',
    ];

    public $orderable = [
        'payment_method_name',
    ];

    protected $primaryKey = 'payment_method_id';

    protected $fillable = [
        'payment_method_name',
    ];

    protected static function newFactory(): Factory
    {
        return PaymentMethodFactory::new();
    }
}
