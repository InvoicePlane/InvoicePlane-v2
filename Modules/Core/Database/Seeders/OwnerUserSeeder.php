<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;

class OwnerUserSeeder extends AbstractSeeder
{
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'super_admin'],
            ['guard_name' => 'web']
        );

        $admin = User::query()->firstOrNew(['email' => 'admin@invoiceplane.com']);

        if ( ! $admin->exists) {
            $admin->fill([
                'name'              => 'Administrator',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();

            $admin->assignRole($adminRole);

            Log::info('Admin user created successfully!');
            Log::info('Email: admin@invoiceplane.com');
            Log::info('Password: password');
        } else {
            Log::info('Admin user already exists.');
        }
    }
}
