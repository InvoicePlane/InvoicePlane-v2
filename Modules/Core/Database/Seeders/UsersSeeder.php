<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Models\User;

use Modules\Core\Models\Company;

use Modules\Core\Database\Seeders\UsersSeeder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Model $model): void {
            /** @var Company $company */
            $company = $model;

            User::factory()
                ->count(random_int(15, 25))
                ->create()
                ->each(function (Model $model) use ($company): void {
                    /** @var User $user */
                    $user = $model;

                    $user->companies()->attach($company->id);
                });
        });
    }
}
