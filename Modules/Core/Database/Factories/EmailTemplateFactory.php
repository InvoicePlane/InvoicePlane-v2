<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'title'      => $this->faker->sentence(),
            'type'       => $this->faker->randomElement(EmailTemplateType::cases())->value,
            'subject'    => $this->faker->word,
            'body'       => '',
            'from_name'  => $this->faker->name(),
            'from_email' => $this->faker->safeEmail(),
            'cc'         => $this->faker->safeEmail(),
            'bcc'        => $this->faker->safeEmail(),
        ];
    }
}
