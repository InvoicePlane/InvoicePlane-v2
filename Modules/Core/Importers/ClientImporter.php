<?php

namespace App\IpModules\Import\Importers;

use App\IpModules\Customers\Models\Customer;
use App\IpModules\CustomFields\Models\CustomField;
use Illuminate\Support\Facades\Validator;

class ClientImporter extends AbstractImporter
{
    public function getFields()
    {
        $fields = [
            'name'        => '* ' . trans('ip.name'),
            'unique_name' => trans('ip.unique_name'),
            'address_1'   => trans('ip.address'),
            'city'        => trans('ip.city'),
            'state'       => trans('ip.state'),
            'zip'         => trans('ip.postal_code'),
            'country'     => trans('ip.country'),
            'phone'       => trans('ip.phone'),
            'fax'         => trans('ip.fax'),
            'mobile'      => trans('ip.mobile'),
            'email'       => trans('ip.email'),
            'web'         => trans('ip.web'),
        ];

        foreach (CustomField::forTable('customers')->get() as $customField) {
            $fields['custom_' . $customField->column_name] = $customField->field_label;
        }

        return $fields;
    }

    public function getMapRules()
    {
        return ['name' => 'required'];
    }

    public function getValidator($input)
    {
        return Validator::make($input, [
            'name'  => 'required',
            'email' => 'email',
        ])->setAttributeNames(['name' => trans('ip.name')]);
    }

    public function importData($input)
    {
        $row = 1;

        $fields       = [];
        $customFields = [];

        foreach ($input as $key => $field) {
            if (is_numeric($field)) {
                if (mb_substr($key, 0, 7) != 'custom_') {
                    $fields[$key] = $field;
                } else {
                    $customFields[mb_substr($key, 7)] = $field;
                }
            }
        }

        $handle = fopen(storage_path('customers.csv'), 'r');

        if ( ! $handle) {
            $this->messages->add('error', 'Could not open the file');

            return false;
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($row !== 1) {
                $record = [];

                $customRecord = [];

                foreach ($fields as $field => $key) {
                    $record[$field] = $data[$key];
                }

                if ($this->validateRecord($record)) {
                    $client = Customer::create($record);

                    if ($customFields) {
                        foreach ($customFields as $field => $key) {
                            $customRecord[$field] = $data[$key];
                        }
                    }
                }
            }

            $row++;
        }

        fclose($handle);

        return true;
    }
}
