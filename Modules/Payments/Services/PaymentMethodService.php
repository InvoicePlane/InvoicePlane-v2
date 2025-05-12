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
            'name' => $data['name'],
        ]);
    }

    public function updatePaymentMethod(PaymentMethod $model, array $data): PaymentMethod
    {
        $model->update([
            'name' => $data['name'],
        ]);

        return $model;
    }
}
