<?php

namespace Modules\Projects\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProjectServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_project(): void
    {
        $project = (new ProjectService())->create(['title' => 'Redesign']);
        $this->assertInstanceOf(Project::class, $project);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_title(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ProjectService())->create([]);
    }
}
