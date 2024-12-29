<?php

namespace Modules\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Expenses\Models\ExpenseVendor;
use Modules\Invoices\Models\Invoice;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $vendorId = ExpenseVendor::create(['name' => $this->faker->word])->vendor_id ?? ExpenseVendor::all()->random()->vendor_id;
        $categoryId = ExpenseCategory::create(['name' => $this->faker->word])->category_id ?? ExpenseCategory::all()->random()->category_id;

        return [
            'client_id'    => Client::factory(),
            'vendor_id'    => $vendorId,
            'invoice_id'   => Invoice::all()->random()->invoice_id,
            'category_id'  => $categoryId,
            'user_id'      => User::factory(),
            'expense_date' => $this->faker->dateTimeBetween('-3 years', '+2 months')->format('Y-m-d H:i:s'),
            'amount'       => $this->faker->numerify('##.##'),
            'description'  => null,
        ];
    }
}
