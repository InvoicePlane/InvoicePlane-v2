<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;

class PaymentService extends BaseService
{
    public function model(): string
    {
        return Payment::class;
        //event(new PaymentWasCreated($payment));
    }

    public function create(array $data): Model
    {
        return parent::create([
            'company_id'        => session('current_company_id'),
            'customer_id'       => $data['customer_id'],
            'user_id'           => auth()->id(),
            'payment_method_id' => $data['payment_method_id'],
            'paid_at'           => $data['paid_at'],
            'amount'            => $data['amount'],
            'notes'             => $data['notes'] ?? null,
        ]);
    }

    public function update(array $data, $model): Model
    {
        $model->update([
            'customer_id'       => $data['customer_id'],
            'payment_method_id' => $data['payment_method_id'],
            'paid_at'           => $data['paid_at'],
            'amount'            => $data['amount'],
            'notes'             => $data['notes'] ?? null,
        ]);

        return $model;
    }
}
