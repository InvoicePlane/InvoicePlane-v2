<?php

namespace Modules\Payments\Services;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Services\PaymentMethodService;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;

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
