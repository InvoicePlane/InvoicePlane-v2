<?php

namespace Modules\Core\Tests\Unit\Services;

use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\DocumentGroupService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentGroupService::class)]
class DocumentGroupServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $service = new DocumentGroupService();

        $group = $service->create(['name' => 'Credit Note'], $company);

        $this->assertInstanceOf(DocumentGroup::class, $group);
        $this->assertEquals('Credit Note', $group->name);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_without_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $service = new DocumentGroupService();

        $this->expectException(InvalidArgumentException::class);
        $service->create([], $company);
    }
}
