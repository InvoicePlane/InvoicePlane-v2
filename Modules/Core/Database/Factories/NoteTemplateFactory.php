<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Company;
use Modules\Core\Models\NoteTemplate;

class NoteTemplateFactory extends AbstractFactory
{
    protected $model = NoteTemplate::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();

        return [
            'company_id'     => $companyId ?? Company::factory(),
            'template_title' => $this->faker->sentence(),
            'template_body'  => $this->faker->paragraph(),
        ];
    }
}
