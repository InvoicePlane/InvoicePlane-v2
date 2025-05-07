<?php

namespace Modules\Core\Tests\Unit\Services;

use DocumentGroupService;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Tests\Unit\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Modules\Core\Tests\AbstractTestCase;

#[CoversClass(DocumentGroupService::class)]
class DocumentGroupServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_document_group(): void
    {
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
        $company = Company::factory()->create();
        $service = new DocumentGroupService();

        $this->expectException(InvalidArgumentException::class);
        $service->create([], $company);
    }
}
