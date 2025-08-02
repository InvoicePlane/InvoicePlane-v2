<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Note;
use Modules\Core\Models\User;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends AbstractFactory
{
    protected $model = Note::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'user_id'      => User::query()->inRandomOrder()->first()->id,
            'noted_at'     => fake()->date(),
            'notable_type' => fake()->word,
            'notable_id'   => null,
            'is_private'   => fake()->boolean(75),
            'title'        => fake()->title,
            'content'      => fake()->word,
        ];
    }
}
