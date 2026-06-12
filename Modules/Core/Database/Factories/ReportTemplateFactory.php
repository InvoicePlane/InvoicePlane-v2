<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportTemplate;

class ReportTemplateFactory extends Factory
{
    protected $model = ReportTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'company_id'    => Company::factory(),
            'name'          => ucfirst($name),
            'slug'          => Str::slug($name),
            'description'   => $this->faker->optional(0.7)->sentence(),
            'template_type' => $this->faker->randomElement(ReportTemplateType::cases()),
            'is_system'     => false,
            'is_active'     => true,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    public function ofType(ReportTemplateType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'template_type' => $type,
        ]);
    }
}
