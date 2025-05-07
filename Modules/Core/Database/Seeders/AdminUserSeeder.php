<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'admin user for InvoicePlane',
            'email'    => 'admin@invoiceplane.com',
            'password' => Hash::make('password'),
        ]);
    }
}
