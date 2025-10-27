<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Upload;

class UploadFactory extends AbstractFactory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'company_id'           => $companyId,
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
