<?php

namespace Modules\Projects\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Customer;
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
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

        /* Arrange */
        $payload = [
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Website Redesign',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseHas('projects', array_merge(
            ['company_id' => $this->company->id],
            [
                'customer_id'    => $payload['customer_id'],
                'project_status' => $payload['project_status'],
                'project_name'   => $payload['project_name'],
                'start_at'       => isset($payload['start_at']) && mb_strlen($payload['start_at']) === 10 ? $payload['start_at'] . ' 00:00:00' : $payload['start_at'],
                'end_at'         => isset($payload['end_at']) && mb_strlen($payload['end_at']) === 10 ? $payload['end_at'] . ' 00:00:00' : $payload['end_at'],
                'description'    => $payload['description'],
            ]
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'company_id'   => $this->company->id,
            'customer_id'  => $customer->id,
            'project_name' => 'Website Redesign',
            'start_at'     => '2025-05-01',
            'end_at'       => '2025-06-01',
            'description'  => 'Redesigning the corporate website',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'customer_id'    => $customer->id,
            'project_name'   => 'Client Redesign',
            'project_status' => ProjectStatus::ACTIVE->value,
            'description'    => 'Modernizing UX',
            'end_at'         => '2025-06-30',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction(TestAction::make('edit')->table($project), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
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

        /* Arrange */
        $payload = [
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Website Redesign',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', array_merge(
            $payload,
            [
                'start_at' => isset($payload['start_at']) ? $this->formatDateForDb($payload['start_at']) : null,
                'end_at'   => isset($payload['end_at']) ? $this->formatDateForDb($payload['end_at']) : null,
            ]
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
    public function it_fails_to_create_project_without_required_status(): void
    {
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
    public function it_fails_to_create_project_without_required_project_name_in_create_form(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::company_name::']);

        $payload = [
            'company_id'     => $this->company->id,
            'customer_id'    => $customer->id,
            'project_status' => 'active',
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
            ->assertStatus(422)
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

        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['client_name' => '::client_name::']);

        /* arrange */
        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => 2,
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

    /**
     * @payload missing: starts_at
     * {
     *   "project_name": "Client Redesign",
     *   "description": "Modernizing UX",
     *   "ends_at": "2025-06-30"
     * }
     */
    public function it_fails_to_create_project_without_required_starts_at(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['client_name' => '::client_name::']);

        $payload = [
            'project_name' => 'Client Redesign',
            'description'  => 'Modernizing UX',
            'ends_at'      => '2025-06-30',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['starts_at']);
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

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $updatedData = [
            'project_name' => '::updated_project_name::',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditProject::class, ['record' => $project->project_id])->set('data.project_name', $updatedData['project_name'])->call('save');

        /* assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('projects', array_merge($updatedData, [
            'project_id' => $project->project_id,
        ]));
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::client_name::']);

        $payload = [
            'customer_id'    => $customer->id,
            'project_name'   => 'Website Redesign',
            'project_status' => ProjectStatus::ON_HOLD->value,
            'description'    => 'Modernizing UX',
            'end_at'         => '2025-02-20',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
    public function it_updates_a_project_via_modal(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create(['company_name' => '::company_name::']);

        $project = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $updatedData = [
            'project_name' => '::updated_project_name::',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProject::class, ['record' => $project->id])
            ->fillForm($updatedData)
            ->call('save');

        /* Assert */
        $component
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction(TestAction::make('delete')->table($project))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion

    /**
     * Format a date string for DB assertion (adds ' 00:00:00' if not present).
     */
    private function formatDateForDb(string $date): string
    {
        return \Illuminate\Support\Str::contains($date, ':') ? $date : $date . ' 00:00:00';
    }
}
