<?php

namespace Modules\Core\Importers;

use Illuminate\Support\Facades\Validator;
use Modules\Expenses\Models\Expense;

class ExpenseImporter extends AbstractImporter
{
    public function getFields()
    {
        $fields = [
            'expensed_at'     => '* ' . trans('ip.date'),
            'category_name'   => '* ' . trans('ip.category'),
            'amount'          => '* ' . trans('ip.amount'),
            'vendor_name'     => trans('ip.vendor'),
            'client_name'     => trans('ip.customer'),
            'description'     => trans('ip.description'),
            'tax'             => trans('ip.tax'),
            'company_profile' => trans('ip.company_profile'),
        ];

        return $fields;
    }

    public function getMapRules()
    {
        return [
            'expensed_at'   => 'required',
            'category_name' => 'required',
            'amount'        => 'required',
        ];
    }

    public function getValidator($input)
    {
        return Validator::make($input, [
            'expensed_at'   => 'required',
            'category_name' => 'required',
            'amount'        => 'required|numeric',
        ])->setAttributeNames([
            'user_id'       => trans('ip.user'),
            'company_id'    => trans('ip.company_profile'),
            'expensed_at'   => trans('ip.date'),
            'category_name' => trans('ip.category'),
            'description'   => trans('ip.description'),
            'amount'        => trans('ip.amount'),
        ]);
    }

    public function importData($input)
    {
        $row = 1;

        $fields = [];

        foreach ($input as $key => $field) {
            if (is_numeric($field)) {
                $fields[$key] = $field;
            }
        }

        $handle = fopen(storage_path('expenses.csv'), 'r');

        if ( ! $handle) {
            $this->messages->add('error', 'Could not open the file');

            return false;
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($row !== 1) {
                $record = [];

                foreach ($fields as $field => $key) {
                    $record[$field] = $data[$key];
                }

                if ($this->validateRecord($record)) {
                    Expense::create($record);
                }
            }

            $row++;
        }

        fclose($handle);

        return true;
    }
}
