<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Model $model): void {
            /** @var Company $company */
            $company = $model;

            User::factory()
                ->count(random_int(5, 10))
                ->create()
                ->each(function (Model $model) use ($company): void {
                    /** @var User $user */
                    $user = $model;

                    $user->companies()->attach($company->id);
                });
        });
    }
}
