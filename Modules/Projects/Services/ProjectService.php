<?php

namespace Modules\Projects\Services;

use Modules\Projects\Services\ProjectService;

use Modules\Projects\Events\ProjectWasUpdated;

use Modules\Projects\Events\ProjectWasCreated;

use Modules\Projects\Models\Project;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends BaseService
{
    public function model(): string
    {
        return Project::class;
    }

    public function create(array $validatedInput): Project
    {
        $project = new Project(
            $validatedInput
        );

        $project->save();

        event(new ProjectWasCreated());

        return $project;
    }

    public function update(array $validatedInput, $projectToUpdate): Model
    {
        $projectToUpdate->fill($validatedInput);

        $projectToUpdate->save();

        event(new ProjectWasUpdated());

        return $projectToUpdate;
    }
}
