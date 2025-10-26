<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BlockDTOTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_id(): void
    {
        $dto = new BlockDTO();
        $dto->setId('block_header_company');

        $this->assertEquals('block_header_company', $dto->getId());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_type(): void
    {
        $dto = new BlockDTO();
        $dto->setType('header_company');

        $this->assertEquals('header_company', $dto->getType());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_position(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $dto = new BlockDTO();
        $dto->setPosition($position);

        $this->assertInstanceOf(GridPositionDTO::class, $dto->getPosition());
        $this->assertEquals(0, $dto->getPosition()->getX());
        $this->assertEquals(0, $dto->getPosition()->getY());
        $this->assertEquals(6, $dto->getPosition()->getWidth());
        $this->assertEquals(4, $dto->getPosition()->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_config(): void
    {
        $config = ['show_vat_id' => true, 'show_phone' => true];
        $dto    = new BlockDTO();
        $dto->setConfig($config);

        $this->assertEquals($config, $dto->getConfig());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_label(): void
    {
        $dto = new BlockDTO();
        $dto->setLabel('Company Header');

        $this->assertEquals('Company Header', $dto->getLabel());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_label_to_null(): void
    {
        $dto = new BlockDTO();
        $dto->setLabel(null);

        $this->assertNull($dto->getLabel());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_is_cloneable(): void
    {
        $dto = new BlockDTO();
        $dto->setIsCloneable(true);

        $this->assertTrue($dto->getIsCloneable());

        $dto->setIsCloneable(false);

        $this->assertFalse($dto->getIsCloneable());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_data_source(): void
    {
        $dto = new BlockDTO();
        $dto->setDataSource('company');

        $this->assertEquals('company', $dto->getDataSource());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_data_source_to_null(): void
    {
        $dto = new BlockDTO();
        $dto->setDataSource(null);

        $this->assertNull($dto->getDataSource());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_is_cloned(): void
    {
        $dto = new BlockDTO();
        $dto->setIsCloned(true);

        $this->assertTrue($dto->getIsCloned());

        $dto->setIsCloned(false);

        $this->assertFalse($dto->getIsCloned());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_cloned_from(): void
    {
        $dto = new BlockDTO();
        $dto->setClonedFrom('block_original');

        $this->assertEquals('block_original', $dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_cloned_from_to_null(): void
    {
        $dto = new BlockDTO();
        $dto->setClonedFrom(null);

        $this->assertNull($dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_create_system_block(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $config = ['show_vat_id' => true];

        $dto = BlockDTO::system('header_company', $position, $config);

        $this->assertEquals('header_company', $dto->getType());
        $this->assertEquals($position, $dto->getPosition());
        $this->assertEquals($config, $dto->getConfig());
        $this->assertTrue($dto->getIsCloneable());
        $this->assertFalse($dto->getIsCloned());
        $this->assertNull($dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_create_cloned_block(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $original = new BlockDTO();
        $original->setId('block_original')
            ->setType('header_company')
            ->setPosition($position)
            ->setConfig(['show_vat_id' => true])
            ->setLabel('Original Label')
            ->setIsCloneable(true)
            ->setDataSource('company')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $cloned = BlockDTO::clonedFrom($original, 'block_cloned');

        $this->assertEquals('block_cloned', $cloned->getId());
        $this->assertEquals('header_company', $cloned->getType());
        $this->assertEquals($position, $cloned->getPosition());
        $this->assertEquals(['show_vat_id' => true], $cloned->getConfig());
        $this->assertEquals('Original Label', $cloned->getLabel());
        $this->assertTrue($cloned->getIsCloneable());
        $this->assertEquals('company', $cloned->getDataSource());
        $this->assertTrue($cloned->getIsCloned());
        $this->assertEquals('block_original', $cloned->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function setters_return_self_for_method_chaining(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $dto = (new BlockDTO())
            ->setId('block_test')
            ->setType('test_type')
            ->setPosition($position)
            ->setConfig(['key' => 'value'])
            ->setLabel('Test Label')
            ->setIsCloneable(true)
            ->setDataSource('test_source')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $this->assertInstanceOf(BlockDTO::class, $dto);
        $this->assertEquals('block_test', $dto->getId());
        $this->assertEquals('test_type', $dto->getType());
    }
}
