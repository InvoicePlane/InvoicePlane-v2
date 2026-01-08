<?php

namespace Modules\Core\Support;

class CustomFields
{
    /**
     * Provide an array of available custom table names.
     *
     * @return array
     */
    public static function tableNames()
    {
        return [
            'customers'          => trans('ip.clients'),
            'companies'          => trans('ip.company_profiles'),
            'expenses'           => trans('ip.expenses'),
            'invoices'           => trans('ip.invoices'),
            'quotes'             => trans('ip.quotes'),
            'recurring_invoices' => trans('ip.recurring_invoices'),
            'payments'           => trans('ip.payments'),
            'users'              => trans('ip.users'),
        ];
    }

    /**
     * Provide an array of available custom field types.
     *
     * @return array
     */
    public static function fieldTypes()
    {
        return [
            'text'     => trans('ip.text'),
            'dropdown' => trans('ip.dropdown'),
            'textarea' => trans('ip.textarea'),
        ];
    }
}
