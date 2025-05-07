<?php

namespace Modules\Core\Importers;

use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Company;
use Modules\Quotes\Models\Quote;

class QuoteImporter extends AbstractImporter
{
    public function getFields()
    {
        return [
            'quoted_at'       => '* ' . trans('ip.date'),
            'company_profile' => '* ' . trans('ip.company_profile'),
            'client_name'     => '* ' . trans('ip.client_name'),
            'number'          => '* ' . trans('ip.quote_number'),
            'group_id'        => trans('ip.group'),
            'expires_at'      => trans('ip.expires'),
            'summary'         => trans('ip.summary'),
            'terms'           => trans('ip.terms_and_conditions'),
        ];
    }

    public function getMapRules()
    {
        return [
            'quoted_at'       => 'required',
            'company_profile' => 'required',
            'client_name'     => 'required',
            'number'          => 'required',
        ];
    }

    public function getValidator($input)
    {
        return Validator::make(
            $input,
            [
                'customer_id' => 'required|integer',
            ]
        );
    }

    public function importData($input)
    {
        $row             = 1;
        $fields          = [];
        $clients         = Customer::select('id', 'unique_name')->get();
        $companyProfiles = Company::get();
        $groups          = Group::get();
        $userId          = auth()->user()->id;

        foreach ($input as $field => $key) {
            if (is_numeric($key)) {
                $fields[$key] = $field;
            }
        }

        $handle = fopen(storage_path('quotes.csv'), 'r');

        if ( ! $handle) {
            $this->messages->add('error', 'Could not open the file');

            return false;
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($row !== 1) {
                $record = [];

                // Create the initial record from the file line
                foreach ($fields as $key => $field) {
                    $record[$field] = $data[$key];
                }

                // Replace the customer name with the customer id
                if ($client = $clients->where('unique_name', $record['client_name'])->first()) {
                    $record['customer_id'] = $client->id;
                } else {
                    $record['customer_id'] = Customer::create(['name' => $record['client_name']])->id;
                }

                unset($record['client_name']);

                // Replace the company profile name with the company profile id
                $companyProfile = $companyProfiles->where('name', $record['company_profile'])->first();

                if ($companyProfile) {
                    $record['company_id'] = $companyProfile->id;
                }

                unset($record['company_profile']);

                // Format the created at date
                if (strtotime($record['quoted_at'])) {
                    $record['quoted_at'] = date('Y-m-d', strtotime($record['quoted_at']));
                }

                // Attempt to format this date if it exists.
                if (isset($record['expires_at']) && strtotime($record['expires_at'])) {
                    $record['expires_at'] = date('Y-m-d', strtotime($record['expires_at']));
                }

                // Attempt to convert the group name to an id if it exists.
                if (isset($record['group_id'])) {
                    $group = $groups->where('name', $record['group_id'])->first();

                    if ($group) {
                        $record['group_id'] = $group->id;
                    }
                }

                // Assign the quote to the current logged in user
                $record['user_id'] = $userId;

                // The record *should* validate, but just in case...
                if ($this->validateRecord($record)) {
                    Quote::create($record);
                }
            }
            $row++;
        }

        fclose($handle);

        return true;
    }
}
