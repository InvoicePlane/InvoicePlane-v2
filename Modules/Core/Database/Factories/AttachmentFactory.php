<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Attachment;
use Modules\Core\Models\User;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends AbstractFactory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::query()->inRandomOrder()->first()->id,
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
