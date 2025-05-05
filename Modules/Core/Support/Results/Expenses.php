<?php

namespace App\IpModules\Exports\Support\Results;

use App\IpModules\Expenses\Models\Expense;

class Expenses implements SourceInterface
{
    public function getResults($params = [])
    {
        return Expense::select(
            'expenses.expensed_at',
            'expenses.description',
            'expenses.amount',
            'customers.name AS client_name',
            'expense_categories.name AS category_name',
            'expense_vendors.name AS vendor_name',
            'invoices.number AS invoice_number',
            'users.name AS user_name',
            'company_profiles.company'
        )
            ->leftJoin('users', 'users.id', '=', 'expenses.user_id')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
            ->leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')
            ->leftJoin('expense_vendors', 'expense_vendors.id', '=', 'expenses.vendor_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'expenses.invoice_id')
            ->join('companies', 'company_profiles.id', '=', 'expenses.company_id')
            ->orderBy('invoices.number')
            ->get()
            ->toArray();
    }
}
