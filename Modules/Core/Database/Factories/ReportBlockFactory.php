<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockType;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportDataSource;
use Modules\Core\Models\ReportBlock;

class ReportBlockFactory extends Factory
{
    protected $model = ReportBlock::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $slug = Str::slug($name) . '-' . Str::random(8);

        return [
            'is_active' => true,
            'is_system' => false,
            'block_type' => $this->faker->randomElement(ReportBlockType::cases()),
            'name' => ucfirst($name),
            'slug' => $slug,
            'filename' => $slug,
            'width' => $this->faker->randomElement(ReportBlockWidth::cases()),
            'data_source' => $this->faker->randomElement(ReportDataSource::cases()),
            'default_band' => $this->faker->randomElement(ReportBand::cases()),
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

    public function width(ReportBlockWidth $width): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => $width,
        ]);
    }
}
