<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;

class ExpensesSeeder extends Seeder
{
    protected array $expenseDescriptions = [
        'Business insurance',
        'Business travel expenses',
        'Client meeting lunch',
        'Conference attendance',
        'Hardware equipment',
        'Internet and phone bills',
        'Marketing campaign',
        'Office supplies purchase',
        'Professional development course',
        'Rent payment',
        'Software subscription renewal',
        'Team building activity',
        'Utilities payment',
        'Vehicle maintenance',
        'Web hosting services',
    ];

    protected array $paymentMethods = [
        'bank_transfer' => 'Bank Transfer',
        'cash'          => 'Cash',
        'check'         => 'Check',
        'credit_card'   => 'Credit Card',
        'other'         => 'Other',
        'paypal'        => 'PayPal',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Expense::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping expenses for company {$company->name} - already has {$existingCount} expenses.");

                return;
            }

            $this->command->info("Creating expenses for company: {$company->name}");

            $categories = $this->getOrCreateExpenseCategories($company->id);

            $users = User::query()->where('company_id', $company->id)->get();

            if ($users->isEmpty()) {
                $this->command->warn("No users found for company {$company->name}. Creating some...");
                $this->call(UsersSeeder::class, ['companyId' => $company->id]);
                $users = User::query()->where('company_id', $company->id)->get();
            }

            $customers = Relation::query()->where('company_id', $company->id)
                ->where('relation_type', 'customer')
                ->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)
                    ->where('relation_type', 'customer')
                    ->get();
            }

            $expenseCount = random_int(20, 50);

            for ($i = 0; $i < $expenseCount; $i++) {
                $this->createExpense($company, $categories->random(), $users->random(), $customers);
            }

            $this->command->info("Created {$expenseCount} expenses for company: {$company->name}");
        });
    }

    protected function getOrCreateExpenseCategories(int $companyId): Collection
    {
        $categories = ExpenseCategory::query()->where('company_id', $companyId)->get();

        if ($categories->isEmpty()) {
            $this->command->warn('No expense categories found. Creating default categories...');

            $defaultCategories = [
                'Bank Fees',
                'Hardware',
                'Insurance',
                'Maintenance',
                'Marketing',
                'Meals & Entertainment',
                'Office Supplies',
                'Other',
                'Professional Services',
                'Rent',
                'Software',
                'Subscriptions',
                'Training',
                'Travel',
                'Utilities',
                'Vehicle',
            ];

            foreach ($defaultCategories as $categoryName) {
                ExpenseCategory::create([
                    'company_id'    => $companyId,
                    'category_name' => $categoryName,
                ]);
            }

            $categories = ExpenseCategory::query()->where('company_id', $companyId)->get();
        }

        return $categories;
    }

    protected function createExpense(Company $company, $category, $user, $customers): void
    {
        $statuses = [
            ExpenseStatus::DRAFT,
            ExpenseStatus::SUBMITTED,
            ExpenseStatus::APPROVED,
            ExpenseStatus::REIMBURSED,
            ExpenseStatus::BILLED,
            ExpenseStatus::PAID,
        ];

        $status        = $statuses[array_rand($statuses)];
        $expenseDate   = now()->subDays(random_int(0, 365));
        $paymentMethod = array_rand($this->paymentMethods);

        $expense = Expense::factory()
            ->for($company)
            ->for($category, 'category')
            ->for($user, 'submitter')
            ->create([
                'expense_number' => $this->generatePaymentReference($paymentMethod),
                'expense_status' => $status->value,
                'expensed_at'    => $expenseDate,
                'expense_amount' => random_int(1000, 100000) / 100,
                'description'    => $this->expenseDescriptions[array_rand($this->expenseDescriptions)],
            ]);
    }

    protected function generatePaymentReference(string $paymentMethod): ?string
    {
        return match($paymentMethod) {
            'bank_transfer' => 'BT-' . mb_strtoupper(mb_substr(md5(rand()), 0, 10)),
            'check'         => 'CHK-' . mb_str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'credit_card'   => 'CC-' . mb_strtoupper(mb_substr(md5(rand()), 0, 8)),
            'paypal'        => 'PP-' . mb_strtoupper(mb_substr(md5(rand()), 0, 12)),
            default         => null,
        };
    }
}
