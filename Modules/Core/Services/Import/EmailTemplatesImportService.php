<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Models\EmailTemplate;

class EmailTemplatesImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_email_templates'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['email_templates']);

        $this->importEmailTemplates();

        return $this->stats;
    }

    private function importEmailTemplates(): void
    {
        $templates = $this->getImportData('ip_email_templates');

        foreach ($templates as $v1Template) {
            EmailTemplate::create([
                'company_id'   => $this->companyId,
                'title'        => $v1Template->email_template_title ?? 'Template',
                'type'         => $v1Template->email_template_type ?? 'default',
                'subject'      => $v1Template->email_template_subject ?? '',
                'body'         => $v1Template->email_template_body ?? '',
                'from_name'    => $v1Template->email_template_from_name ?? null,
                'from_email'   => $v1Template->email_template_from_email ?? null,
            ]);

            $this->stats['email_templates']++;
        }
    }
}
