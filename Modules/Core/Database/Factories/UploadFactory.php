<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Upload;

/**
 * @extends Factory<\Modules\Core\Models\Upload>
 */
class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'           => $company->id,
            'user_id'              => \Modules\Core\Models\User::query()->inRandomOrder()->first()->id,
            'uploadable_type'      => null,
            'uploadable_id'        => null,
            'upload_original_name' => fake()->word,
            'upload_stored_name'   => fake()->word,
            'upload_mime_type'     => fake()->word,
            'upload_url_key'       => fake()->word,
            'upload_disk'          => fake()->word,
            'file_description'     => null,
        ];
    }
}
