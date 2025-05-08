<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_projects(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        $customer = Relation::factory()->create();

        session(['current_company_id' => $company->id]);

        $this->actingAs($user);

        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProjects::class);

        /* assert */
        $component->assertSuccessful()->assertSee('Example');
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

        /* arrange */

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProject::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors()->assertSee('Website Redesign');

        $this->assertDatabaseHas('projects', $payload);
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

        /* arrange */

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProject::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertStatus(422)->assertHasFormErrors(['project_name' => 'required']);
    }

    /**
     * @test
     *
     * @payload
     * * {
     * *    "project_name": "Updated Project Name"
     * * }
     */
    public function it_updates_a_project(): void
    {
        $this->markTestIncomplete();

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

        /** act */
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
     * "name": "Example",
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
            'name'           => 'Example',
            'start_at'       => '2025-04-30',
            'end_at'         => '2025-04-30',
            'description'    => 'Example',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditProject::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

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
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        $project = Project::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ManageProjects::class)->callTableAction('delete', $project->project_id);

        /* assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseMissing('projects', ['project_id' => $project->project_id]);
    }
    // endregion

    // region Custom Tests
    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.projects.assign_client')
     *
     * @skip Not implemented yet
     */
    public function it_projects_assign_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('needs assignClient action');
        // $this->authenticate();
        $client  = Relation::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Relation::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ManageProjects::class)->callTableAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    public function it_fails_to_assign_client_without_project_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('needs assignClient action');
        // $this->authenticate();
        $client  = Relation::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Relation::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ManageProjects::class)->callTableAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertStatus(422)->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    public function it_projects_change_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('needs assignClient action');        // $this->authenticate();
        $client  = Relation::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Relation::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ManageProjects::class)->callTableAction('assignClient', $client2->client_id);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    public function it_fails_to_change_project_client_without_client_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('needs assignClient action');
        // $this->authenticate();
        $client  = Relation::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Relation::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ManageProjects::class)->callTableAction('assignClient');

        /* assert */
        $component->assertStatus(422)->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }
    // endregion
}
