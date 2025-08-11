<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Setting;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends AbstractFactory
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
