<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\User;

use Modules\Core\Database\Factories\UserFactory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Core\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        $verifiedAt = $this->faker->dateTimeBetween('-18 months', '-3 months');

        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => $verifiedAt,
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
