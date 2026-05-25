<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateFactory extends AbstractFactory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'company_id' => $company->id,
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
