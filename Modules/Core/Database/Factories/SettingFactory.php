<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Setting;

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
