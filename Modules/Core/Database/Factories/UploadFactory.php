<?php

namespace Modules\Core\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Upload;
use Modules\Core\Models\User;

class UploadFactory extends AbstractFactory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $company    = $this->resolveCompany() ?? Company::factory()->create();
        $uploadable = Relation::factory()->for($company)->create();

        return [
            'company_id'           => $company->id,
            'user_id'              => User::query()->inRandomOrder()->first()->id,
            'uploadable_type'      => $uploadable->getMorphClass(),
            'uploadable_id'        => $uploadable->id,
            'upload_original_name' => fake()->word,
            'upload_stored_name'   => fake()->word,
            'upload_mime_type'     => fake()->word,
            'upload_url_key'       => fake()->unique()->word,
            'upload_disk'          => fake()->word,
            'file_description'     => fake()->sentence(),
        ];
    }
}
