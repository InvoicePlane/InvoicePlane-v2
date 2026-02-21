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
                'company_id'            => $this->companyId,
                'email_template_title'  => $v1Template->email_template_title ?? 'Template',
                'email_template_type'   => $v1Template->email_template_type ?? 'default',
                'email_template_subject' => $v1Template->email_template_subject ?? '',
                'email_template_body'   => $v1Template->email_template_body ?? '',
                'email_template_from_name' => $v1Template->email_template_from_name ?? null,
                'email_template_from_email' => $v1Template->email_template_from_email ?? null,
            ]);

            $this->stats['email_templates']++;
        }
    }
}
