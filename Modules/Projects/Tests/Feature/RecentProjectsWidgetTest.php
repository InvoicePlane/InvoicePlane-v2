<?php

namespace Modules\Projects\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Filament\Company\Widgets\RecentProjectsWidget;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentProjectsWidget::class)]
class RecentProjectsWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_projects_index_page(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();

        Project::factory()
            ->for($this->company)
            ->create([
                'project_number' => 'PRJ-0001',
                'customer_id'    => $customer->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentProjectsWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(ProjectResource::getUrl('index'), false);
    }

    #[Test]
    #[Group('smoke')]
    public function it_lists_newer_projects_before_older_projects(): void
    {
        /* Arrange */
        $customer     = Relation::factory()->for($this->company)->customer()->create();
        $olderProject = Project::factory()
            ->for($this->company)
            ->create([
                'project_number' => 'PRJ-OLDER',
                'customer_id'    => $customer->id,
            ]);
        $newerProject = Project::factory()
            ->for($this->company)
            ->create([
                'project_number' => 'PRJ-NEWER',
                'customer_id'    => $customer->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentProjectsWidget::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$newerProject, $olderProject], inOrder: true);
    }
}
