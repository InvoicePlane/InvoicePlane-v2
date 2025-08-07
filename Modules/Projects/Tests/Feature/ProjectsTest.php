<?php

namespace Modules\Projects\Tests\Feature;

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
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create(['company_name' => 'Test Client']);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
            'project_name'   => 'Test Project',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-05-30',
            'description'    => 'Test Description',
        ];

        $project = Project::factory()->for($company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class, ['tenant' => Str::lower($company->search_code)]);

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('projects', $payload);
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
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create(['company_name' => 'Test Client']);

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
            ['company_id' => $company->id],
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
        $this->markTestIncomplete('Have to pass null as project_status and figure out how tryFrom reacts to that');

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['company_name' => '::client_name::']);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
            'project_status' => 'test',
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
            ->callMountedAction();

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
    public function it_fails_to_create_project_through_a_modal_without_required_project_name(): void
    {
        $this->markTestIncomplete('Need to validate emptyness of project_name where it can be null but still is required');

        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['company_name' => '::client_name::']);

        /* arrange */
        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
            'project_status' => 'active',
            'start_at'       => '2025-05-01',
            'end_at'         => '2025-06-01',
            'description'    => 'Redesigning the corporate website',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertStatus(422)
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
        $component->assertHasFormErrors(['start_at']);
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
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create(['company_name' => 'Test Client']);
        $project  = Project::factory()->for($company)->create([
            'project_name'   => 'Old Project Name',
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
        ]);

        $updateData = [
            'project_name' => 'Updated Project Name',
            'description'  => 'Updated description',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->id])
            ->fillForm($updateData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('projects', array_merge(
            ['id' => $project->id],
            $updateData
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "customer_id": "Value",
     * "project_status": "Value",
     * "project_name": "Example",
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_project_through_a_modal_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        //$this->actingAs(User::factory()->create());

        $record = Project::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'customer_id'    => 'Value',
            'project_status' => 'Value',
            'project_name'   => 'Example',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-04-30',
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $record->getKey()])
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
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
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create(['company_name' => 'Test Client']);

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
        $this->markTestIncomplete('Have to pass null as project_status and figure out how tryFrom reacts to that');

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['company_name' => '::company_name::']);

        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => 2,
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
        $this->markTestIncomplete('Need to validate emptyness of project_name where it can be null but still is required');

        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->create(['company_name' => '::company_name::']);

        /* arrange */
        $payload = [
            'company_id'     => $company->id,
            'customer_id'    => 2,
            'project_status' => 'active',
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
            ->assertHasFormErrors(['project_name' => 'required']);
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
        $component->assertHasFormErrors(['start_at']);
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
        $this->markTestIncomplete();

        /* arrange */
        $client = Relation::factory()->create(['company_name' => '::company_name::']);

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
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('projects', array_merge($updatedData, [
            'project_id' => $project->project_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "customer_id": "Value",
     * "project_status": "Value",
     * "project_name": "Example",
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_project_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = Project::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'customer_id'    => 'Value',
            'project_status' => 'Value',
            'project_name'   => 'Example',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-04-30',
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProject::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_project(): void
    {
        $this->markTestIncomplete('DeleteAction missing on ProjectsTable');

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create(['company_name' => 'Test Client']);
        $project  = Project::factory()->for($company)->create([
            'project_name'   => 'Project to Delete',
            'customer_id'    => $customer->id,
            'project_status' => ProjectStatus::ACTIVE->value,
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('delete', ['record' => $project]);

        /* assert */
        $component->assertSuccessful();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_projects_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s project.');
    }
    # endregion

    # region spicy
    #[Test]
    #[Group('crud')]
    /**
     * route('filament.ivpl.resources.filament.resources.projects.assign_client').
     */
    public function it_can_assign_clients_to_project(): void
    {
        $this->markTestIncomplete('needs assignClient action if still needed since it can be done on the project form');

        /* arrange */
        // $this->authenticate();
        $customer = Customer::factory()->create();
        $project  = Project::factory()->create(['client_id' => $client->client_id]);
        $client2  = Relation::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->getKey()])
            ->callMountedAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_assign_client_without_project_id(): void
    {
        $this->markTestIncomplete('needs assignClient action if still needed since it can be done on the project form');

        /* arrange */
        $customer = Customer::factory()->create();
        $project  = Project::factory()->create(['client_id' => $client->client_id]);
        $client2  = Relation::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->getKey()])
            ->callMountedAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertStatus(422)->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_projects_change_client(): void
    {
        $this->markTestIncomplete('needs assignClient action if still needed since it can be done on the project form');

        /* arrange */
        $customer = Customer::factory()->create();
        $project  = Project::factory()->create(['client_id' => $client->client_id]);
        $client2  = Relation::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->getKey()])
            ->callMountedAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_change_project_client_without_client_id(): void
    {
        $this->markTestIncomplete('needs assignClient action if still needed since it can be done on the project form');

        /* arrange */
        $customer = Customer::factory()->create();
        $project  = Project::factory()->create(['client_id' => $client->client_id]);
        $client2  = Relation::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->getKey()])
            ->callMountedAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertStatus(422)->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }
    # endregion
}
