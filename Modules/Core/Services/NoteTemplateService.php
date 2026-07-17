<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\NoteTemplate;
use Throwable;

class NoteTemplateService extends BaseService
{
    public function model(): string
    {
        return NoteTemplate::class;
    }

    public function createNoteTemplate(array $data): NoteTemplate
    {
        return NoteTemplate::query()->create([
            'company_id'     => $this->getCompanyId(),
            'template_title' => $data['template_title'],
            'template_body'  => $data['template_body'],
        ]);
    }

    public function updateNoteTemplate(NoteTemplate $model, array $data): NoteTemplate
    {
        $model->update([
            'template_title' => $data['template_title'],
            'template_body'  => $data['template_body'],
        ]);

        return $model;
    }

    public function deleteNoteTemplate(NoteTemplate $noteTemplate, array $data = []): NoteTemplate
    {
        DB::beginTransaction();
        try {
            $noteTemplate->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $noteTemplate;
    }
}
