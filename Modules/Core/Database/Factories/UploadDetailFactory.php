<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UploadDetail;

/**
 * @extends Factory<UploadDetail>
 */
class UploadDetailFactory extends AbstractFactory
{
    protected $model = UploadDetail::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'company_id'          => $companyId,
            'upload_id'           => \Modules\Core\Models\Upload::query()->inRandomOrder()->first()->id,
            'upload_detail_key'   => fake()->word,
            'upload_detail_value' => fake()->word,
        ];
    }
}
