<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;
use Modules\Core\Models\User;
use Modules\Products\Models\Product;

class AbstractFactory extends Factory
{
    public function definition() {}

    protected function findOrCreateWithCompany(string $modelClass, array $where, array $attributes = [])
    {
        if ( ! method_exists($modelClass, 'query')) {
            throw new InvalidArgumentException("Class {$modelClass} must be an Eloquent model with a query() method.");
        }

        return $modelClass::query()
            ->where($where)
            ->inRandomOrder()
            ->firstOrCreate($where, $attributes);
    }

    protected function findOrCreateRandomUser(int $companyId): User
    {
        $user = User::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->inRandomOrder()
            ->first();

        if ( ! $user) {
            $user = User::factory()
                ->create();

            $user->companies()->attach($companyId);
        }

        return $user;
    }

    protected function findOrCreateRandomProduct(int $companyId): Product
    {
        $product = Product::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $product) {
            $product = Product::factory()
                ->state(['company_id' => $companyId])
                ->create();
        }

        return $product;
    }
}
