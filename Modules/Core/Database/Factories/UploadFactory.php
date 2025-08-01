<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Upload;

/**
 * @extends Factory<Upload>
 */
class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

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
