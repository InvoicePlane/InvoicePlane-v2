<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the admin role exists
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );

        // Create or get the first company
        $company = Company::query()->firstOrCreate(
            ['search_code' => 'ivplv2'],
            [
                'search_code'      => 'ivplv2',
                'name'             => 'InvoicePlane Corporation',
                'slug'             => 'invoiceplane-corporation',
                'vat_number'       => 'US0123456789',
                'id_number'        => '1234567890',
                'coc_number'       => '12345678',
                'quote_template'   => 'default',
                'invoice_template' => 'default',
            ]
        );

        // Create the admin user if it doesn't exist
        $admin = User::query()->firstOrNew(['email' => 'admin@invoiceplane.com']);

        if ( ! $admin->exists) {
            $admin->fill([
                'name'              => 'Administrator',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();

            // Assign admin role to the user
            $admin->assignRole($adminRole);

            // Attach user to the company using Eloquent relationship
            $admin->companies()->attach($company->id);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@invoiceplane.com');
            $this->command->info('Password: password');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
