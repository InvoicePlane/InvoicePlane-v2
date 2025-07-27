<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class UsersSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected static ?string $hashedPassword = null;

    public function run(?int $companyId = null): void
    {
        $company = $companyId ? Company::query()->find($companyId) : Company::query()->first();

        if ( ! $company) {
            $this->command->warn('No company found. Please run CompaniesSeeder first.');

            return;
        }

        $adminCount = 2;
        $userCount  = 8;

        $this->command->info("Creating {$adminCount} customer admins and {$userCount} customers for company {$company->name} ({$company->id})");

        for ($i = 1; $i <= $adminCount; $i++) {
            $email = "customeradmin{$i}@company{$company->id}.com";
            $name  = 'Customer Admin ' . $i;

            $this->createUser($company, $email, $name, [UserRole::CUSTOMER_ADMIN->value]);
        }

        for ($i = 1; $i <= $userCount; $i++) {
            $email = "customer{$i}@company{$company->id}.com";
            $name  = 'Customer ' . $i;

            $this->createUser($company, $email, $name, [UserRole::CUSTOMER->value]);
        }

        $this->command->info('Users seeded successfully.');
        $this->command->info('All users have the password: password');
    }

    protected function getPassword(): string
    {
        if (self::$hashedPassword === null) {
            self::$hashedPassword = bcrypt('password');
        }

        return self::$hashedPassword;
    }

    protected function createUser(Company $company, string $email, string $name, array $roles): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        if ( ! $user->exists) {
            $user->fill([
                'name'              => $name,
                'password'          => $this->getPassword(),
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
