<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\ReportBuilder\MasonDocumentConverter;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class MasonDocumentConverterTest extends AbstractTestCase
{
    #[Test]
    public function it_converts_band_entries_to_mason_state_with_labels_and_previews(): void
    {
        /* Act */
        $state = MasonDocumentConverter::toMasonState([
            ['brick' => 'header_company', 'width' => 'half', 'config' => ['show_vat_id' => true]],
        ]);

        /* Assert */
        $this->assertCount(1, $state);
        $this->assertSame('masonBrick', $state[0]['type']);
        $this->assertSame('header_company', $state[0]['attrs']['id']);
        $this->assertSame('half', $state[0]['attrs']['config'][MasonDocumentConverter::WIDTH_KEY]);
        $this->assertTrue($state[0]['attrs']['config']['show_vat_id']);
        $this->assertNotEmpty($state[0]['attrs']['label']);
        $this->assertNotEmpty($state[0]['attrs']['preview']);
    }

    #[Test]
    public function it_skips_unknown_bricks_when_building_mason_state(): void
    {
        /* Act */
        $state = MasonDocumentConverter::toMasonState([
            ['brick' => 'does_not_exist', 'width' => 'full', 'config' => []],
        ]);

        /* Assert */
        $this->assertSame([], $state);
    }

    #[Test]
    public function it_round_trips_entries_through_mason_state(): void
    {
        /* Arrange */
        $entries = [
            ['brick' => 'header_company', 'width' => 'half', 'config' => ['show_vat_id' => false]],
            ['brick' => 'header_client', 'width' => 'full', 'config' => []],
        ];

        /* Act */
        $roundTripped = MasonDocumentConverter::toBandEntries(
            MasonDocumentConverter::toMasonState($entries),
        );

        /* Assert */
        $this->assertSame('header_company', $roundTripped[0]['brick']);
        $this->assertSame('half', $roundTripped[0]['width']);
        $this->assertSame(['show_vat_id' => false], $roundTripped[0]['config']);
        $this->assertSame('header_client', $roundTripped[1]['brick']);
        $this->assertSame('full', $roundTripped[1]['width']);
        $this->assertArrayNotHasKey(MasonDocumentConverter::WIDTH_KEY, $roundTripped[0]['config']);
    }

    #[Test]
    public function it_ignores_non_brick_nodes_in_mason_state(): void
    {
        /* Act */
        $entries = MasonDocumentConverter::toBandEntries([
            ['type' => 'paragraph', 'content' => []],
            ['type' => 'masonBrick', 'attrs' => ['id' => 'spacer', 'config' => ['height' => 30]]],
        ]);

        /* Assert */
        $this->assertCount(1, $entries);
        $this->assertSame('spacer', $entries[0]['brick']);
    }

    #[Test]
    public function it_unwraps_a_content_wrapped_mason_document(): void
    {
        /* Act */
        $entries = MasonDocumentConverter::toBandEntries([
            'type'    => 'doc',
            'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'page_break', 'config' => []]],
            ],
        ]);

        /* Assert */
        $this->assertCount(1, $entries);
        $this->assertSame('page_break', $entries[0]['brick']);
    }

    #[Test]
    public function it_defaults_invalid_widths_to_full(): void
    {
        /* Act */
        $entries = MasonDocumentConverter::toBandEntries([
            ['type' => 'masonBrick', 'attrs' => ['id' => 'spacer', 'config' => [MasonDocumentConverter::WIDTH_KEY => 'bogus']]],
        ]);

        /* Assert */
        $this->assertSame('full', $entries[0]['width']);
    }
}
