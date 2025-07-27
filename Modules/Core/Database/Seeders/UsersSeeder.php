<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class UsersSeeder extends Seeder
{
    public function run(?int $companyId = null): void
    {
        // Get or create the company
        $company = $companyId ? Company::find($companyId) : Company::first();

        if ( ! $company) {
            $this->command->warn('No company found. Please run CompaniesSeeder first.');

            return;
        }

        // Define users with their roles
        $users = [
            [
                'name'  => 'Manager',
                'email' => 'manager@company' . $company->id . '.com',
                'roles' => [UserRole::CUSTOMER_ADMIN->value],
            ],
            [
                'name'  => 'Staff',
                'email' => 'staff@company' . $company->id . '.com',
                'roles' => [UserRole::CUSTOMER->value],
            ],
        ];

        foreach ($users as $userData) {
            // Create or update the user
            $user = User::query()->firstOrNew(['email' => $userData['email']]);

            if ( ! $user->exists) {
                $user->fill([
                    'name'              => $userData['name'] . ' ' . $company->id,
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ])->save();

                // Assign roles
                $user->syncRoles($userData['roles']);

                // Attach to company if not already attached
                if ( ! $user->companies()->where('company_id', $company->id)->exists()) {
                    $user->companies()->attach($company->id);
                }

                $this->command->info(sprintf(
                    'Created %s with email %s (Password: password)',
                    $userData['name'],
                    $userData['email']
                ));
            } else {
                $this->command->info(sprintf('User %s already exists', $userData['email']));
            }
        }

        $this->command->info('Users seeded successfully.');
        $this->command->info('All users have the password: password');
    }
}
