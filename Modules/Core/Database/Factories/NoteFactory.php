<?php

namespace Modules\Core\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Note;
use Modules\Core\Models\User;

class NoteFactory extends AbstractFactory
{
    protected $model = Note::class;

    public function definition(): array
    {
        $company = $this->resolveCompany() ?? Company::factory()->create();
        $notable = Relation::factory()->for($company)->create();

        return [
            'company_id'   => $company->id,
            'user_id'      => User::query()->inRandomOrder()->first()->id,
            'noted_at'     => fake()->date(),
            'notable_type' => $notable->getMorphClass(),
            'notable_id'   => $notable->id,
            'is_private'   => fake()->boolean(75),
            'title'        => fake()->word,
            'content'      => fake()->paragraph(),
        ];
    }
}
