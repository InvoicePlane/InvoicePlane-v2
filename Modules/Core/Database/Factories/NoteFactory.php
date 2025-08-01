<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Note;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

        return [
            'company_id'   => $company->id,
            'user_id'      => \Modules\Core\Models\User::query()->inRandomOrder()->first()->id,
            'noted_at'     => fake()->date(),
            'notable_type' => fake()->word,
            'notable_id'   => null,
            'is_private'   => fake()->boolean(75),
            'title'        => fake()->title,
            'content'      => fake()->word,
        ];
    }
}
