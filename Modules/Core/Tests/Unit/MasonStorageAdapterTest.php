<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Services\MasonStorageAdapter;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class MasonStorageAdapterTest extends AbstractTestCase
{
    protected MasonStorageAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new MasonStorageAdapter();
    }

    #[Test]
    public function it_converts_mason_json_to_block_dtos(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_abc123',
                        'config' => [
                            'show_vat_id' => true,
                            'show_phone' => true,
                            'font_size' => 10,
                        ],
                        'label' => 'Company Header',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'detail_items_xyz789',
                        'config' => [
                            'show_description' => true,
                            'show_quantity' => true,
                        ],
                        'label' => 'Line Items',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);

        /* Assert */
        $this->assertIsArray($blocks);
        $this->assertCount(2, $blocks);
        $this->assertInstanceOf(BlockDTO::class, $blocks['header_company_abc123']);
        $this->assertInstanceOf(BlockDTO::class, $blocks['detail_items_xyz789']);
    }

    #[Test]
    public function it_extracts_correct_type_from_mason_brick_id(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_abc123',
                        'config' => [],
                        'label' => 'Company Header',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $block = reset($blocks);

        /* Assert */
        $this->assertEquals('header_company', $block->getType());
    }

    #[Test]
    public function it_preserves_config_from_mason_brick(): void
    {
        /* Arrange */
        $expectedConfig = [
            'show_vat_id' => true,
            'show_phone' => false,
            'font_size' => 12,
            'text_align' => 'center',
        ];

        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_test',
                        'config' => $expectedConfig,
                        'label' => 'Test Block',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $block = reset($blocks);

        /* Assert */
        $this->assertEquals($expectedConfig, $block->getConfig());
    }

    #[Test]
    public function it_converts_block_dtos_to_mason_json(): void
    {
        /* Arrange */
        $position = GridPositionDTO::create(0, 0, 12, 4);
        
        $block1 = new BlockDTO();
        $block1->setId('header_company_abc')
            ->setType('header_company')
            ->setPosition($position)
            ->setConfig(['show_vat_id' => true])
            ->setLabel('Company Header')
            ->setIsCloneable(false)
            ->setDataSource('company')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $block2 = new BlockDTO();
        $block2->setId('footer_totals_xyz')
            ->setType('footer_totals')
            ->setPosition($position)
            ->setConfig(['show_tax' => true])
            ->setLabel('Totals')
            ->setIsCloneable(false)
            ->setDataSource('invoice')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        /* Act */
        $masonJson = $this->adapter->blocksToMason([$block1, $block2]);
        $decoded = json_decode($masonJson, true);

        /* Assert */
        $this->assertIsArray($decoded);
        $this->assertEquals('doc', $decoded['type']);
        $this->assertCount(2, $decoded['content']);
        $this->assertEquals('masonBrick', $decoded['content'][0]['type']);
        $this->assertEquals('header_company_abc', $decoded['content'][0]['attrs']['id']);
    }

    #[Test]
    public function it_returns_empty_array_for_invalid_mason_json(): void
    {
        /* Arrange */
        $invalidJson = 'not valid json';

        /* Act */
        $blocks = $this->adapter->masonToBlocks($invalidJson);

        /* Assert */
        $this->assertIsArray($blocks);
        $this->assertEmpty($blocks);
    }

    #[Test]
    public function it_returns_empty_array_for_mason_json_without_content(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);

        /* Assert */
        $this->assertIsArray($blocks);
        $this->assertEmpty($blocks);
    }

    #[Test]
    public function it_assigns_correct_data_source_based_on_type(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_test',
                        'config' => [],
                        'label' => 'Company',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_client_test',
                        'config' => [],
                        'label' => 'Client',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'detail_items_test',
                        'config' => [],
                        'label' => 'Items',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);

        /* Assert */
        $this->assertEquals('company', $blocks['header_company_test']->getDataSource());
        $this->assertEquals('client', $blocks['header_client_test']->getDataSource());
        $this->assertEquals('items', $blocks['detail_items_test']->getDataSource());
    }

    #[Test]
    public function it_roundtrip_conversion_preserves_data(): void
    {
        /* Arrange */
        $position = GridPositionDTO::create(0, 0, 12, 4);
        
        $originalBlock = new BlockDTO();
        $originalBlock->setId('header_company_test')
            ->setType('header_company')
            ->setPosition($position)
            ->setConfig([
                'show_vat_id' => true,
                'font_size' => 10,
            ])
            ->setLabel('Company Header')
            ->setIsCloneable(false)
            ->setDataSource('company')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        /* Act */
        $masonJson = $this->adapter->blocksToMason([$originalBlock]);
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $convertedBlock = reset($blocks);

        /* Assert */
        $this->assertEquals($originalBlock->getId(), $convertedBlock->getId());
        $this->assertEquals($originalBlock->getType(), $convertedBlock->getType());
        $this->assertEquals($originalBlock->getConfig(), $convertedBlock->getConfig());
        $this->assertEquals($originalBlock->getLabel(), $convertedBlock->getLabel());
    }
}
