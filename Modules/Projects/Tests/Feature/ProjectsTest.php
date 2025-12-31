<?php

namespace Modules\Projects\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Filament\Company\Resources\Projects\Pages\CreateProject;
use Modules\Projects\Filament\Company\Resources\Projects\Pages\EditProject;
use Modules\Projects\Filament\Company\Resources\Projects\Pages\ListProjects;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListProjects::class)]
class ProjectsTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_projects(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => 'Test Client']);

        $payload = [
            'company_id'     => $this->company->id,
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Test Project',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-05-30',
            'description'    => 'Test Description',
        ];

        $project = Project::factory()->for($this->company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('projects', [
            'company_id'     => $payload['company_id'],
            'customer_id'    => $payload['customer_id'],
            'project_status' => $payload['project_status'],
            'project_name'   => $payload['project_name'],
            'start_at'       => $payload['start_at'] . ' 00:00:00',
            'end_at'         => $payload['end_at'] . ' 00:00:00',
            'description'    => $payload['description'],
        ]);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "project_name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_creates_a_project_through_a_modal(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['company_name' => 'Test Client']);

        /* arrange */
        $payload = [
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Website Redesign',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('projects', array_merge(
            ['company_id' => $this->company->id],
            $payload
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_fails_to_create_project_through_a_modal_without_required_status(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'company_id'   => $this->company->id,
            'customer_id'  => $customer->id,
            'project_name' => 'Website Redesign',
            'start_at'     => '2025-05-01',
            'end_at'       => '2025-06-01',
            'description'  => 'Redesigning the corporate website',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertHasFormErrors(['project_status' => 'required']);

        $this->assertDatabaseMissing('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: project_name
     * {
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_fails_to_create_project_through_a_modal_without_required_project_name(): void
    {
        $customer = Relation::factory()
            ->for($this->company, 'company')
            ->create(['company_name' => '::client_name::']);

        $payload = [
            'customer_id'    => $customer->id,
            'project_status' => 'active',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['project_name' => 'required']);

        $this->assertDatabaseMissing('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: start_at
     * {
     *   "project_name": "Client Redesign",
     *   "description": "Modernizing UX",
     *   "ends_at": "2025-06-30"
     * }
     */
    public function it_fails_to_create_project_through_a_modal_without_required_start_at(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'customer_id'    => $customer->id,
            'project_name'   => 'Client Redesign',
            'project_status' => ProjectStatus::ACTIVE->value,
            'description'    => 'Modernizing UX',
            'end_at'         => '2025-06-30',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertHasFormErrors(['start_at']);

        $this->assertDatabaseMissing('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *    "project_name": "Updated Project Name"
     * }
     */
    public function it_updates_a_project_through_a_modal(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => 'Test Client']);
        $project  = Project::factory()->for($this->company)->create([
            'project_name'   => 'Old Project Name',
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
        ]);

        $updatedData = [
            'project_name' => 'Updated Project Name',
            'description'  => 'Updated description',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction(TestAction::make('edit')->table($project), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('projects', array_merge(
            ['id' => $project->id],
            $updatedData
        ));
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "project_name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_creates_a_project(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['company_name' => 'Test Client']);

        /* arrange */
        $payload = [
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Website Redesign',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];
        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_fails_to_create_project_without_required_status(): void
    {
        /* arrange */
        $customer = Relation::factory()
            ->for($this->company)
            ->create(['company_name' => '::company_name::']);

        $payload = [
            'company_id'   => $this->company->id,
            'customer_id'  => $customer->id,
            'project_name' => 'Website Redesign',
            'start_at'     => '2025-05-01',
            'end_at'       => '2025-06-01',
            'description'  => 'Redesigning the corporate website',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertHasFormErrors(['project_status' => 'required']);

        $this->assertDatabaseMissing('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: project_name
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_status": "active",
     *   "project_name": "Website Redesign",
     *   "start_at": "2025-05-01",
     *   "end_at": "2025-06-01",
     *   "description": "Redesigning the corporate website"
     * }
     */
    public function it_fails_to_create_project_without_required_project_name(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::company_name::']);

        $payload = [
            'company_id'     => $this->company->id,
            'customer_id'    => $customer->id,
            'project_status' => 'active',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        $component
            ->assertHasFormErrors(['project_name' => 'required']);

        $this->assertDatabaseMissing('projects', [
            'customer_id'  => $customer->id,
            'project_name' => 'Website Redesign',
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: start_at
     * {
     *   "project_name": "Client Redesign",
     *   "description": "Modernizing UX",
     *   "ends_at": "2025-06-30"
     * }
     */
    public function it_fails_to_create_project_without_required_start_at(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'customer_id'    => $customer->id,
            'project_name'   => 'Website Redesign',
            'project_status' => ProjectStatus::ON_HOLD->value,
            'description'    => 'Modernizing UX',
            'end_at'         => '2025-02-20',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertHasFormErrors(['start_at']);

        $this->assertDatabaseMissing('projects', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *    "project_name": "Updated Project Name"
     * }
     */
    public function it_updates_a_project(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::company_name::']);

        $project = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $updatedData = [
            'project_name' => '::updated_project_name::',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProject::class, ['record' => $project->id])
            ->fillForm($updatedData)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', array_merge($updatedData, [
            'id' => $project->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_project(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['company_name' => 'Test Client']);
        $project  = Project::factory()->for($this->company)->create([
            'project_name'   => 'Project to Delete',
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction(TestAction::make('delete')->table($project))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion
}
