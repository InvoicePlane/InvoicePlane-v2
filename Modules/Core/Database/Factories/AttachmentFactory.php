<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Attachment;

/**
 * @extends Factory<\Modules\Core\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'user_id'           => \Modules\Core\Models\User::query()->inRandomOrder()->first()->id,
            'attachable_id'     => null,
            'attachable_type'   => fake()->word,
            'client_visibility' => fake()->boolean(95),
            'filename'          => fake()->word,
            'mimetype'          => fake()->word,
            'size'              => fake()->randomNumber(),
            'url_key'           => fake()->word,
        ];
    }
}
