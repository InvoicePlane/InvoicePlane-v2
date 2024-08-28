<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Import;

class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition()
    {
        return [
            'import_date' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
