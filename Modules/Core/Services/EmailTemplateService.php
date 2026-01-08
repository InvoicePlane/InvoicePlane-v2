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

    public function createEmailTemplate(array $data): EmailTemplate
    {
        return EmailTemplate::query()->create([
            'company_id' => $this->getCompanyId() ?? 1,
            'type'       => $data['type'],
            'title'      => $data['title'],
            'subject'    => $data['subject'],
            'body'       => $data['body'] ?? '',
            'from_name'  => $data['from_name'],
            'from_email' => $data['from_email'],
            'cc'         => $data['cc'] ?? null,
            'bcc'        => $data['bcc'] ?? null,
        ]);
    }

    public function updateEmailTemplate(EmailTemplate $emailTemplateToUpdate, $data): Model
    {
        $emailTemplateToUpdate->update([
            'type'       => $data['type'],
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'from_name'  => $data['from_name'],
            'from_email' => $data['from_email'],
            'cc'         => $data['cc'] ?? null,
            'bcc'        => $data['bcc'] ?? null,
        ]);

        return $emailTemplateToUpdate;
    }
}
