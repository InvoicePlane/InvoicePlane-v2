<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'email_template_title'        => $this->faker->word(),
            'email_template_type'         => $this->faker->word(),
            'email_template_body'         => $this->faker->text,
            'email_template_subject'      => $this->faker->word(),
            'email_template_from_name'    => $this->faker->word(),
            'email_template_from_email'   => $this->faker->word(),
            'email_template_cc'           => $this->faker->word(),
            'email_template_bcc'          => $this->faker->word(),
            'email_template_pdf_template' => $this->faker->word(),
        ];
    }
}
