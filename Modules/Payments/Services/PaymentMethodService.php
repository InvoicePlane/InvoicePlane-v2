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

    public function createPaymentMethod(array $data): Model
    {
        return $this->create([
            'payment_method_name' => $data['payment_method_name'],
        ]);
    }

    public function updatePaymentMethod(PaymentMethod $model, array $data): PaymentMethod
    {
        $model->update([
            'payment_method_name' => $data['payment_method_name'],
        ]);

        return $model;
    }
}
