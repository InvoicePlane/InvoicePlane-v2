<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class UsersSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $company = $companyId ? Company::find($companyId) : Company::first();

        if ( ! $company) {
            $this->command->warn('No company found. Please run CompaniesSeeder first.');

            return;
        }

        $userCount  = rand(10, 20);
        $adminCount = max(1, (int) ($userCount * 0.2));

        $this->command->info("Creating {$userCount} users for company {$company->name} ({$company->id})");
        $this->command->info("  - {$adminCount} will be customer admins");
        $this->command->info('  - ' . ($userCount - $adminCount) . ' will be regular customers');

        for ($i = 1; $i <= $adminCount; $i++) {
            $email = "admin{$i}@company{$company->id}.com";
            $name  = 'Admin ' . $i;

            $this->createUser($company, $email, $name, [UserRole::CUSTOMER_ADMIN->value]);
        }

        for ($i = 1; $i <= ($userCount - $adminCount); $i++) {
            $email = "user{$i}@company{$company->id}.com";
            $name  = 'User ' . $i;

            $this->createUser($company, $email, $name, [UserRole::CUSTOMER->value]);
        }

        $this->command->info('Users seeded successfully.');
        $this->command->info('All users have the password: password');
    }

    protected function createUser(Company $company, string $email, string $name, array $roles): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        if ( ! $user->exists) {
            $user->fill([
                'name'              => $name . ' ' . $company->id,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles($roles);

            if ( ! $user->companies()->where('company_id', $company->id)->exists()) {
                $user->companies()->attach($company->id);
            }

            $this->command->info(sprintf(
                'Created %s with email %s (Password: password)',
                $name,
                $email
            ));
        } else {
            $this->command->info(sprintf('User %s already exists', $email));
        }
    }
}
