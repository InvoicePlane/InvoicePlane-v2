<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodService extends BaseService
{
    public function model(): string
    {
        return PaymentMethod::class;
    }

    public function create(array $validatedInput): PaymentMethod
    {
        $paymentMethod = new PaymentMethod($validatedInput);

        $paymentMethod->save();

        return $paymentMethod;
    }

    public function update(array $validatedInput, $paymentMethodToUpdate): Model
    {
        $paymentMethodToUpdate->fill($validatedInput);

        $paymentMethodToUpdate->save();

        return $paymentMethodToUpdate;
    }
}
