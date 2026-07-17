<?php

namespace Modules\Projects\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Projects\Filament\Company\Widgets\RecentTasksWidget;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentTasksWidget::class)]
class RecentTasksWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_tasks_index_page(): void
    {
        /* Arrange */
        Task::factory()
            ->for($this->company)
            ->create(['task_number' => 'TSK-0001']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentTasksWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(TaskResource::getUrl('index'), false);
    }
}
