<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\UploadDetail;

/**
 * @extends Factory<UploadDetail>
 */
class UploadDetailFactory extends Factory
{
    protected $model = UploadDetail::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'          => $company->id,
            'upload_id'           => \Modules\Core\Models\Upload::query()->inRandomOrder()->first()->id,
            'upload_detail_key'   => fake()->word,
            'upload_detail_value' => fake()->word,
        ];
    }
}
