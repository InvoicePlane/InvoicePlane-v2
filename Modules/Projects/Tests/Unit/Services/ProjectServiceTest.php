<?php

namespace Modules\Projects\Services;

use Modules\Projects\Services\ProjectService;

use Modules\Projects\Models\Project;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProjectServiceTest extends TestCase
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
