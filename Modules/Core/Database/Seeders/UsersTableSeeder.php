<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(25)->create();
    }
}
