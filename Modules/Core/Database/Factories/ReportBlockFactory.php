<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\ReportBlock;

class ReportBlockFactory extends Factory
{
    protected $model = ReportBlock::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $blockType = Str::slug($name, '_');

        return [
            'is_active' => true,
            'is_system' => false,
            'block_type' => $blockType,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'filename' => Str::slug($name),
            'width' => $this->faker->randomElement(ReportBlockWidth::cases()),
            'data_source' => $this->faker->randomElement(['company', 'invoice', 'client', 'custom']),
            'default_band' => $this->faker->randomElement(['header', 'group_header', 'details', 'group_footer', 'footer']),
            'config' => [],
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

    public function withConfig(array $config): static
    {
        return $this->state(fn (array $attributes) => [
            'config' => $config,
        ]);
    }
}
