<?php

namespace Modules\Projects\Repositories;

use Modules\Projects\Models\Project;

class ProjectRepository
{
    public function searchForSelect(string $search): array
    {
        return Project::query()
            ->with('customer')
            ->where('project_name', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Project $p) => [
                $p->id => "{$p->project_name} – {$p->customer?->company_name}",
            ])->toArray();
    }

    public function findForSelect($id): string
    {
        if ( ! $id) {
            return '';
        }

        $project = Project::with('customer')->find($id);

        return $project ? "{$project->project_name} – {$project->customer?->company_name}" : '';
    }
}
