<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Tests\TestCase;
use Modules\ReportBuilder\Transformers\BlockTransformer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BlockTransformerTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function it_can_transform_array_to_dto(): void
    {
        $blockData = [
            'id'          => 'block_header_company',
            'type'        => 'header_company',
            'position'    => [
                'x'      => 0,
                'y'      => 0,
                'width'  => 6,
                'height' => 4,
            ],
            'config'      => [
                'show_vat_id' => true,
                'show_phone'  => true,
            ],
            'label'       => 'Company Header',
            'isCloneable' => true,
            'dataSource'  => 'company',
            'isCloned'    => false,
            'clonedFrom'  => null,
        ];

        $dto = BlockTransformer::toDTO($blockData);

        $this->assertInstanceOf(BlockDTO::class, $dto);
        $this->assertEquals('block_header_company', $dto->getId());
        $this->assertEquals('header_company', $dto->getType());
        $this->assertInstanceOf(GridPositionDTO::class, $dto->getPosition());
        $this->assertEquals(0, $dto->getPosition()->getX());
        $this->assertEquals(0, $dto->getPosition()->getY());
        $this->assertEquals(6, $dto->getPosition()->getWidth());
        $this->assertEquals(4, $dto->getPosition()->getHeight());
        $this->assertEquals(['show_vat_id' => true, 'show_phone' => true], $dto->getConfig());
        $this->assertEquals('Company Header', $dto->getLabel());
        $this->assertTrue($dto->getIsCloneable());
        $this->assertEquals('company', $dto->getDataSource());
        $this->assertFalse($dto->getIsCloned());
        $this->assertNull($dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_uses_defaults_for_missing_array_values(): void
    {
        $blockData = [
            'id'   => 'block_test',
            'type' => 'test_type',
        ];

        $dto = BlockTransformer::toDTO($blockData);

        $this->assertEquals('block_test', $dto->getId());
        $this->assertEquals('test_type', $dto->getType());
        $this->assertInstanceOf(GridPositionDTO::class, $dto->getPosition());
        $this->assertEquals(0, $dto->getPosition()->getX());
        $this->assertEquals(0, $dto->getPosition()->getY());
        $this->assertEquals(1, $dto->getPosition()->getWidth());
        $this->assertEquals(1, $dto->getPosition()->getHeight());
        $this->assertEquals([], $dto->getConfig());
        $this->assertNull($dto->getLabel());
        $this->assertFalse($dto->getIsCloneable());
        $this->assertNull($dto->getDataSource());
        $this->assertFalse($dto->getIsCloned());
        $this->assertNull($dto->getClonedFrom());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_transform_dto_to_array(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $dto = new BlockDTO();
        $dto->setId('block_header_company')
            ->setType('header_company')
            ->setPosition($position)
            ->setConfig(['show_vat_id' => true])
            ->setLabel('Company Header')
            ->setIsCloneable(true)
            ->setDataSource('company')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $array = BlockTransformer::toArray($dto);

        $this->assertIsArray($array);
        $this->assertEquals('block_header_company', $array['id']);
        $this->assertEquals('header_company', $array['type']);
        $this->assertIsArray($array['position']);
        $this->assertEquals(0, $array['position']['x']);
        $this->assertEquals(0, $array['position']['y']);
        $this->assertEquals(6, $array['position']['width']);
        $this->assertEquals(4, $array['position']['height']);
        $this->assertEquals(['show_vat_id' => true], $array['config']);
        $this->assertEquals('Company Header', $array['label']);
        $this->assertTrue($array['isCloneable']);
        $this->assertEquals('company', $array['dataSource']);
        $this->assertFalse($array['isCloned']);
        $this->assertNull($array['clonedFrom']);
    }

    #[Test]
    #[Group('unit')]
    public function it_can_transform_dto_to_json_pretty(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $dto = new BlockDTO();
        $dto->setId('block_test')
            ->setType('test_type')
            ->setPosition($position)
            ->setConfig(['key' => 'value'])
            ->setLabel(null)
            ->setIsCloneable(false)
            ->setDataSource(null)
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $json = BlockTransformer::toJson($dto, true);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('block_test', $decoded['id']);
        $this->assertEquals('test_type', $decoded['type']);
        $this->assertStringContainsString("\n", $json);
    }

    #[Test]
    #[Group('unit')]
    public function it_can_transform_dto_to_json_compact(): void
    {
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $dto = new BlockDTO();
        $dto->setId('block_test')
            ->setType('test_type')
            ->setPosition($position)
            ->setConfig([])
            ->setLabel(null)
            ->setIsCloneable(false)
            ->setDataSource(null)
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $json = BlockTransformer::toJson($dto, false);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('block_test', $decoded['id']);
        $this->assertStringNotContainsString("\n    ", $json);
    }

    #[Test]
    #[Group('unit')]
    public function it_can_transform_array_collection_to_dto_collection(): void
    {
        $blocks = [
            [
                'id'          => 'block_1',
                'type'        => 'type_1',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => [],
                'label'       => null,
                'isCloneable' => true,
                'dataSource'  => null,
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
            [
                'id'          => 'block_2',
                'type'        => 'type_2',
                'position'    => ['x' => 6, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => [],
                'label'       => null,
                'isCloneable' => true,
                'dataSource'  => null,
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        $dtos = BlockTransformer::toArrayCollection($blocks);

        $this->assertIsArray($dtos);
        $this->assertCount(2, $dtos);
        $this->assertInstanceOf(BlockDTO::class, $dtos[0]);
        $this->assertInstanceOf(BlockDTO::class, $dtos[1]);
        $this->assertEquals('block_1', $dtos[0]->getId());
        $this->assertEquals('block_2', $dtos[1]->getId());
        $this->assertEquals('type_1', $dtos[0]->getType());
        $this->assertEquals('type_2', $dtos[1]->getType());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_handle_empty_array_collection(): void
    {
        $dtos = BlockTransformer::toArrayCollection([]);

        $this->assertIsArray($dtos);
        $this->assertCount(0, $dtos);
    }

    #[Test]
    #[Group('unit')]
    public function roundtrip_conversion_preserves_data(): void
    {
        $originalData = [
            'id'          => 'block_roundtrip',
            'type'        => 'footer_totals',
            'position'    => ['x' => 10, 'y' => 20, 'width' => 8, 'height' => 3],
            'config'      => ['show_tax' => true, 'currency' => 'USD'],
            'label'       => 'Totals Section',
            'isCloneable' => true,
            'dataSource'  => 'invoice',
            'isCloned'    => true,
            'clonedFrom'  => 'block_original_totals',
        ];

        $dto           = BlockTransformer::toDTO($originalData);
        $convertedData = BlockTransformer::toArray($dto);

        $this->assertEquals($originalData['id'], $convertedData['id']);
        $this->assertEquals($originalData['type'], $convertedData['type']);
        $this->assertEquals($originalData['position'], $convertedData['position']);
        $this->assertEquals($originalData['config'], $convertedData['config']);
        $this->assertEquals($originalData['label'], $convertedData['label']);
        $this->assertEquals($originalData['isCloneable'], $convertedData['isCloneable']);
        $this->assertEquals($originalData['dataSource'], $convertedData['dataSource']);
        $this->assertEquals($originalData['isCloned'], $convertedData['isCloned']);
        $this->assertEquals($originalData['clonedFrom'], $convertedData['clonedFrom']);
    }
}
