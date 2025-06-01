<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Setting;

/**
 * @extends Factory<\Modules\Core\Models\Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'setting_key'   => fake()->word,
            'setting_value' => fake()->word,
        ];
    }
}
