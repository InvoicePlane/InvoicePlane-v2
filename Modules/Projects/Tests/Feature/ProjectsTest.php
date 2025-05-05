<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Filament\Company\Resources\ProjectResource;
use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\CreateProject;
use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\EditProject;
use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\ListProjects;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProjectResource::class)]
class ProjectsTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_projects(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        $customer = Relation::factory()->create();

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE,
            'project_name'   => 'Example',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-04-30',
            'description'    => 'Example',
        ];

        Project::query()->create($payload);

        Livewire::test(ListProjects::class)
            ->assertSuccessful()
            ->assertSee('Example');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_creates_a_project(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => 2,
            'project_status' => 'active',
            'name'           => 'Website Redesign',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        Livewire::test(CreateProject::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertSee('Website Redesign');
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_fails_to_create_project_without_project_name(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => 2,
            'project_status' => 'active',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        Livewire::test(CreateProject::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['project_name' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Projects\Filament\Company\Resources\ProjectResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "customer_id": "Value",
     * "project_status": "Value",
     * "name": "Example",
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_project_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Project::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'customer_id'    => 'Value',
            'project_status' => 'Value',
            'name'           => 'Example',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-04-30',
            'description'    => 'Example',
        ];

        Livewire::test(EditProject::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }
    // endregion
}
