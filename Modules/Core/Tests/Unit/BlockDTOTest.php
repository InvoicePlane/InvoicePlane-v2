<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BlockDTOTest extends AbstractAdminPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_id(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setId('block_company_header');

        /* assert */
        $this->assertEquals('block_company_header', $dto->getId());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_type(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setType('company_header');

        /* assert */
        $this->assertEquals('company_header', $dto->getType());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_position(): void
    {
        /* arrange */
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* act */
        $dto = new BlockDTO();
        $dto->setPosition($position);

        /* assert */
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
        /* arrange */
        $config = ['show_vat_id' => true];

        /* act */
        $dto = new BlockDTO();
        $dto->setConfig($config);

        /* assert */
        $this->assertEquals($config, $dto->getConfig());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_label(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setLabel('Company Header');

        /* assert */
        $this->assertEquals('Company Header', $dto->getLabel());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_label_to_null(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setLabel(null);

        /* assert */
        $this->assertNull($dto->getLabel());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_is_cloneable(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setIsCloneable(true);

        /* assert */
        $this->assertTrue($dto->getIsCloneable());
        $dto->setIsCloneable(false);
        $this->assertFalse($dto->getIsCloneable());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_data_source(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setDataSource('company');

        /* assert */
        $this->assertEquals('company', $dto->getDataSource());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_data_source_to_null(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setDataSource(null);

        /* assert */
        $this->assertNull($dto->getDataSource());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_is_cloned(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setIsCloned(true);

        /* assert */
        $this->assertTrue($dto->getIsCloned());
        $dto->setIsCloned(false);
        $this->assertFalse($dto->getIsCloned());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_cloned_from(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setClonedFrom('block_original');

        /* assert */
        $this->assertEquals('block_original', $dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_cloned_from_to_null(): void
    {
        /* arrange */
        $dto = new BlockDTO();

        /* act */
        $dto->setClonedFrom(null);

        /* assert */
        $this->assertNull($dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_create_system_block(): void
    {
        /* arrange */
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);
        $config = ['show_vat_id' => true];

        /* act */
        $dto = BlockDTO::system('company_header', $position, $config);

        /* assert */
        $this->assertEquals('company_header', $dto->getType());
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
        /* arrange */
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* act */
        $original = new BlockDTO();
        $original->setId('block_original')
            ->setType('company_header')
            ->setPosition($position)
            ->setConfig(['show_vat_id' => true])
            ->setLabel('Original Label')
            ->setIsCloneable(true)
            ->setDataSource('company')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $cloned = BlockDTO::clonedFrom($original, 'block_cloned');

        /* assert */
        $this->assertEquals('block_cloned', $cloned->getId());
        $this->assertEquals('company_header', $cloned->getType());
        $this->assertEquals($position, $cloned->getPosition());
        $this->assertEquals(['show_vat_id' => true], $cloned->getConfig());
        $this->assertEquals('Original Label', $cloned->getLabel());
        $this->assertTrue($cloned->getIsCloneable());
        $this->assertEquals('company', $cloned->getDataSource());
        $this->assertTrue($cloned->getIsCloned());
        $this->assertEquals('block_original', $cloned->getClonedFrom());
        // Verify deep copy: mutating original position should not affect clone
        $position->setX(10);
        $this->assertEquals(10, $original->getPosition()->getX());
        $this->assertEquals(0, $cloned->getPosition()->getX());
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

    #[Test]
    #[Group('unit')]
    public function it_creates_block_dto(): void
    {
        /* arrange */
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* act */
        $dto = new BlockDTO();
        $dto->setId('test_block')
            ->setType('company_header')
            ->setPosition($position)
            ->setConfig(['key' => 'value'])
            ->setLabel('Test Block')
            ->setIsCloneable(true);

        /* assert */
        $this->assertInstanceOf(BlockDTO::class, $dto);
        $this->assertEquals('test_block', $dto->getId());
        $this->assertEquals('company_header', $dto->getType());
        $this->assertEquals($position, $dto->getPosition());
        $this->assertEquals(['key' => 'value'], $dto->getConfig());
        $this->assertEquals('Test Block', $dto->getLabel());
        $this->assertTrue($dto->getIsCloneable());
    }
}
