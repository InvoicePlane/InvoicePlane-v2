<?php

namespace Modules\Core\Importers;

use Illuminate\Support\Facades\Validator;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;

class PaymentImporter extends AbstractImporter
{
    private $paymentValidator;

    public function getFields()
    {
        return [
            'invoice_id'     => '* ' . trans('ip.invoice_number'),
            'paid_at'        => '* ' . trans('ip.date'),
            'amount'         => '* ' . trans('ip.amount'),
            'payment_method' => '* ' . trans('ip.payment_method'),
            'note'           => trans('ip.note'),
        ];
    }

    public function getMapRules()
    {
        return [
            'invoice_id'     => 'required',
            'paid_at'        => 'required',
            'amount'         => 'required',
            'payment_method' => 'required',
        ];
    }

    public function getValidator($input)
    {
        return Validator::make($input, [
            'invoice_id'     => 'required',
            'payment_method' => 'required',
            'paid_at'        => 'required',
            'amount'         => 'required|numeric',
        ]);
    }

    public function importData($input)
    {
        $row = 1;

        $fields = [];

        foreach ($input as $field => $key) {
            if (is_numeric($key)) {
                $fields[$key] = $field;
            }
        }

        $handle = fopen(storage_path('payments.csv'), 'r');

        if ( ! $handle) {
            $this->messages->add('error', 'Could not open the file');

            return false;
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($row !== 1) {
                $record = [];

                foreach ($fields as $key => $field) {
                    $record[$field] = $data[$key];
                }

                // Attempt to format the date, otherwise use today
                if (strtotime($record['paid_at'])) {
                    $record['paid_at'] = date('Y-m-d', strtotime($record['paid_at']));
                } else {
                    $record['paid_at'] = date('Y-m-d');
                }

                // Transform the invoice number to the id
                $record['invoice_id'] = Invoice::where('number', $record['invoice_id'])->first()->id;

                // Transform the payment method to the id
                if ($record['payment_method'] != 'NULL') {
                    $record['payment_method'] = PaymentMethod::firstOrCreate(['name' => $record['payment_method_id']])->id;
                } else {
                    $record['payment_method_id'] = PaymentMethod::firstOrCreate(['name' => 'Other'])->id;
                }

                if ( ! isset($record['note'])) {
                    $record['note'] = '';
                }

                if ($this->validateRecord($record)) {
                    Payment::create($record);
                }
            }
            $row++;
        }

        fclose($handle);

        return true;
    }
}
