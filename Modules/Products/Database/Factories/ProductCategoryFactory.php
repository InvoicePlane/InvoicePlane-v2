<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Products\Models\ProductCategory;

/**
 * @extends Factory<\Modules\Products\Models\ProductCategory>
 */
class ProductCategoryFactory extends AbstractFactory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
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
            'category_name' => $this->faker->randomElement($categories),
            'description'   => null,
        ];
    }
}
