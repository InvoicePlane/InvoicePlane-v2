<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateService extends BaseService
{
    public function model(): string
    {
        return EmailTemplate::class;
    }

    public function create(array $validatedInput): EmailTemplate
    {
        $emailTemplate = new EmailTemplate(
            $validatedInput
        );

        $emailTemplate->save();

        return $emailTemplate;
    }

    public function update(array $validatedInput, $emailTemplateToUpdate): Model
    {
        $emailTemplateToUpdate->fill($validatedInput);

        $emailTemplateToUpdate->save();

        return $emailTemplateToUpdate;
    }
}
