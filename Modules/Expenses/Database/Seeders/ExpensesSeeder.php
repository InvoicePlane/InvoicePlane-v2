<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Database\Eloquent\Collection;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;

class ExpensesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
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

            $users = $company->users;

            if ($users->isEmpty()) {
                $this->command->warn("No users found for company {$company->name}. Creating some...");
                $this->call(UsersSeeder::class, ['companyId' => $company->id]);
                $users = $company->users->count() > 0
                    ? $company->users
                    : collect([User::factory()->for($company)->create()]);
            }

            $customers = Relation::where('company_id', $company->id)
                ->where('relation_type', RelationType::CUSTOMER->value)
                ->where('relation_status', 'active')
                ->get();

            if ($customers->isEmpty()) {
                $customers = collect([
                    Relation::factory()->for($company)->create([
                        'relation_type'   => RelationType::CUSTOMER->value,
                        'relation_status' => 'active',
                    ]),
                ]);
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
            ->for($category, 'expenseCategory')
            ->for($user, 'user')
            ->create([
                'expense_number' => $this->generatePaymentReference($paymentMethod, $company->id),
                'expense_status' => $status->value,
                'expensed_at'    => $expenseDate,
                'expense_amount' => random_int(1000, 100000) / 100,
                'description'    => $this->expenseDescriptions[array_rand($this->expenseDescriptions)],
            ]);
    }

    protected function generatePaymentReference(string $paymentMethod, int $companyId): string
    {
        $prefix = match($paymentMethod) {
            'bank_transfer' => 'BT-',
            'check'         => 'CHK-',
            'credit_card'   => 'CC-',
            'paypal'        => 'PP-',
            default         => 'EXP-',
        } . date('Y') . '-';

        $lastExpense = Expense::query()
            ->where('company_id', $companyId)
            ->where('expense_number', 'like', $prefix . '%')
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($lastExpense) {
            $lastNumber = (int) str_replace($prefix, '', $lastExpense->expense_number);

            return $prefix . mb_str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }
}
