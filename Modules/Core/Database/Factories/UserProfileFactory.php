<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UserProfile;

/**
 * @extends Factory<\Modules\Core\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id'       => \Modules\Core\Models\User::query()->inRandomOrder()->first()->id,
            'user_phone'    => fake()->optional()->word,
            'user_mobile'   => fake()->optional()->word,
            'user_language' => fake()->word,
            'user_web'      => fake()->optional()->word,
            'user_vat_id'   => fake()->optional()->word,
            'user_tax_code' => fake()->optional()->word,
            'user_iban'     => fake()->optional()->word,
        ];
    }
}
