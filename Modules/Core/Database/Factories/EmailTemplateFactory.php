<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Models\EmailTemplate;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends AbstractFactory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

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
