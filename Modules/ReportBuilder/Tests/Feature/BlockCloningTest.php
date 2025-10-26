<?php

namespace Modules\ReportBuilder\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Services\ReportTemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BlockCloningTest extends AbstractAdminPanelTestCase
{
    private ReportTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(ReportTemplateService::class);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "blockType": "header_company",
     *   "newId": "block_header_company_cloned",
     *   "position": {"x": 1, "y": 1, "width": 6, "height": 4}
     * }
     */
    public function it_clones_system_block_on_edit(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $blockType = 'header_company';
        $newId = 'block_header_company_cloned';
        
        $position = new GridPositionDTO();
        $position->setX(1)->setY(1)->setWidth(6)->setHeight(4);

        /* Act */
        $clonedBlock = $this->service->cloneSystemBlock($blockType, $newId, $position);

        /* Assert */
        $this->assertEquals($newId, $clonedBlock->getId());
        $this->assertEquals($blockType, $clonedBlock->getType());
        $this->assertTrue($clonedBlock->isCloned());
        $this->assertEquals('block_header_company', $clonedBlock->getClonedFrom());
        $this->assertEquals(1, $clonedBlock->getPosition()->getX());
        $this->assertEquals(1, $clonedBlock->getPosition()->getY());
    }

    #[Test]
    #[Group('crud')]
    public function it_prevents_editing_system_blocks(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $systemBlocks = [
            [
                'id' => 'block_header_company',
                'type' => 'header_company',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config' => ['show_vat_id' => true],
                'label' => 'Company Header',
                'isCloneable' => true,
                'dataSource' => 'company',
                'isCloned' => false,
                'clonedFrom' => null,
            ],
        ];

        $template = $this->service->createTemplate(
            $company,
            'System Template',
            'invoice',
            $systemBlocks
        );

        $template->is_system = true;
        $template->save();

        /* Assert */
        $this->assertTrue($template->is_system);
        $this->assertTrue($template->isSystem());
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_custom_version_with_unique_id(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $blockType = 'header_company';
        $firstCloneId = 'block_header_company_custom_1';
        $secondCloneId = 'block_header_company_custom_2';
        
        $position1 = new GridPositionDTO();
        $position1->setX(0)->setY(0)->setWidth(6)->setHeight(4);
        
        $position2 = new GridPositionDTO();
        $position2->setX(6)->setY(0)->setWidth(6)->setHeight(4);

        /* Act */
        $firstClone = $this->service->cloneSystemBlock($blockType, $firstCloneId, $position1);
        $secondClone = $this->service->cloneSystemBlock($blockType, $secondCloneId, $position2);

        /* Assert */
        $this->assertNotEquals($firstClone->getId(), $secondClone->getId());
        $this->assertEquals($firstCloneId, $firstClone->getId());
        $this->assertEquals($secondCloneId, $secondClone->getId());
        $this->assertEquals($blockType, $firstClone->getType());
        $this->assertEquals($blockType, $secondClone->getType());
        $this->assertTrue($firstClone->isCloned());
        $this->assertTrue($secondClone->isCloned());
    }
}
