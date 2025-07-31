<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductCategory;
use RuntimeException;

/**
 * @extends Factory<\Modules\Products\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $company = $this->company ?? Company::query()
            ->inRandomOrder()
            ->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for ProductCategory factory');
        }

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
            'category_name' => $this->faker->randomElement($categories),
            'description'   => null,
        ];
    }
}
