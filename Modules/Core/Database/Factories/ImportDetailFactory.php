<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Import;
use Modules\Core\Models\ImportDetail;

class ImportDetailFactory extends Factory
{
    protected $model = ImportDetail::class;

    public function definition()
    {
        return [
            'import_id'         => Import::all()->random()->import_id,
            'import_lang_key'   => $this->faker->word(),
            'import_table_name' => $this->faker->word(),
            'import_record_id'  => $this->faker->randomDigitNotNull,
        ];
    }
}
