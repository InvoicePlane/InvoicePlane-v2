<?php

namespace Modules\Products\Database\Factories;

use Modules\Products\Database\Factories\ProductCategoryFactory;

use Modules\Products\Models\ProductCategory;

use Modules\Projects\Models\Project;

use Modules\Core\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->first()
            ?: Company::factory()->create();

        static $categories = [
            'Accounting Services',
            'Cloud Hosting',
            'Consulting Services',
            'Design Services',
            'Hardware Products',
            'Installation Services',
            'IT Support',
            'Legal Services',
            'Maintenance Contracts',
            'Marketing Services',
            'Office Supplies',
            'Project Management',
            'Software Licenses',
            'Subscription Services',
            'Training Programs',
        ];

        return [
            'company_id'    => $company->id,
            'category_name' => $this->faker->unique()->randomElement($categories),
            'description'   => null,
        ];
    }
}
