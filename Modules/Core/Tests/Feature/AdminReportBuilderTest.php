<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Filament\Admin\Pages\ReportBuilder;
use Modules\Core\Mason\Bricks\HeaderCompanyBrick;
use Modules\Core\Mason\Bricks\SpacerBrick;
use Modules\Core\Filament\Admin\Pages\ReportTemplates;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class AdminReportBuilderTest extends AbstractAdminPanelTestCase
{
    protected ReportTemplateStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);

        $this->storage = new ReportTemplateStorage();

        $this->artisan('reports:sync-system');
    }

    #[Test]
    public function it_lists_system_templates_on_the_admin_list_page(): void
    {
        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ReportTemplates::class)
            ->assertSuccessful()
            ->assertSee('Default Invoice')
            ->assertSee('Default Quote');
    }

    #[Test]
    public function it_opens_the_builder_for_a_system_template_with_five_bands(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default'])
            ->assertSuccessful();

        /* Assert */
        $bands = $component->get('data.bands');
        $this->assertSame(
            ['header', 'group_header', 'details', 'group_footer', 'footer'],
            array_keys($bands),
        );
        $this->assertSame('header_company', $bands['header'][0]['attrs']['id']);
        $this->assertSame('detail_items', $bands['details'][0]['attrs']['id']);
    }

    #[Test]
    public function it_returns_not_found_for_an_unknown_template(): void
    {
        /* Assert */
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'nope']);
    }

    #[Test]
    public function it_saves_the_edited_bands_back_to_disk(): void
    {
        /* Arrange */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        $bands             = $component->get('data.bands');
        $bands['header'][] = [
            'type'  => 'masonBrick',
            'attrs' => ['id' => 'spacer', 'config' => ['height' => 33]],
        ];

        /* Act */
        $component->set('data.bands', $bands)->call('save')->assertHasNoErrors();

        /* Assert */
        $saved      = $this->storage->load('system', 'default', ReportTemplateType::INVOICE);
        $lastHeader = end($saved['bands']['header']);
        $this->assertSame('spacer', $lastHeader['brick']);
        $this->assertSame(['height' => 33], $lastHeader['config']);
    }

    #[Test]
    public function it_lifts_the_block_width_out_of_the_config_when_saving(): void
    {
        /* Arrange */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        $bands             = $component->get('data.bands');
        $bands['header'][] = [
            'type'  => 'masonBrick',
            'attrs' => ['id' => 'spacer', 'config' => ['height' => 12, '_width' => 'two_thirds']],
        ];

        /* Act */
        $component->set('data.bands', $bands)->call('save')->assertHasNoErrors();

        /* Assert */
        $saved      = $this->storage->load('system', 'default', ReportTemplateType::INVOICE);
        $lastHeader = end($saved['bands']['header']);

        $this->assertSame('two_thirds', $lastHeader['width']);
        $this->assertArrayNotHasKey('_width', $lastHeader['config']);
    }

    #[Test]
    public function it_hands_the_stored_width_back_to_the_canvas_on_load(): void
    {
        /* Arrange */
        $this->storage->save(
            'system',
            'default',
            ['name' => 'Default Invoice', 'type' => 'invoice'],
            ['header' => [['brick' => 'header_company', 'width' => 'half', 'config' => []]]],
            ReportTemplateType::INVOICE,
        );

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        /* Assert */
        $bands = $component->get('data.bands');
        $this->assertSame('half', $bands['header'][0]['attrs']['config']['_width']);
    }

    #[Test]
    public function it_previews_an_inserted_brick_with_the_builder_rendering(): void
    {
        /* Arrange */
        $config    = ['_width' => 'half', 'height' => 24];
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        /* Act */
        $component->callAction(
            TestAction::make('handleBrick')->schemaComponent('bands.header'),
            data: $config,
            arguments: ['id' => 'spacer', 'mode' => 'insert', 'dragPosition' => 0],
        );

        /* Assert — the canvas preview must be the builder rendering, never
           toHtml(), which is the print output and needs entity data. */
        $inserted = $component->get('data.bands')['header'][0];

        $this->assertSame('spacer', $inserted['attrs']['id']);
        $this->assertSame(
            SpacerBrick::toPreviewHtml($config),
            base64_decode($inserted['attrs']['preview']),
        );
        $this->assertNotSame(
            SpacerBrick::toHtml($config),
            base64_decode($inserted['attrs']['preview']),
        );
    }

    #[Test]
    public function it_previews_template_via_modal_action_using_builder_rendering(): void
    {
        /* Arrange */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        /* Act */
        $component->mountAction('preview');

        /* Assert */
        $action       = $component->instance()->previewAction();
        $modalContent = (string) $action->getModalContent();

        $headerCompanyConfig = $component->get('data.bands')['header'][0]['attrs']['config'] ?? [];

        $this->assertStringContainsString(
            (string) HeaderCompanyBrick::toPreviewHtml($headerCompanyConfig),
            $modalContent,
        );
        $this->assertStringNotContainsString(
            (string) HeaderCompanyBrick::toHtml($headerCompanyConfig),
            $modalContent,
        );
    }

    public static function bandWidthsProvider(): array
    {
        return [
            'half and half' => [
                [
                    ['brick' => 'header_company', 'width' => 'half', 'config' => []],
                    ['brick' => 'header_client', 'width' => 'half', 'config' => []],
                ],
                ['flex: 0 0 50%', 'flex: 0 0 50%'],
            ],
            'one third and two thirds' => [
                [
                    ['brick' => 'header_company', 'width' => 'one_third', 'config' => []],
                    ['brick' => 'header_client', 'width' => 'two_thirds', 'config' => []],
                ],
                ['flex: 0 0 33.33%', 'flex: 0 0 66.66%'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('bandWidthsProvider')]
    public function it_renders_preview_modal_with_correct_flex_width_wrappers(array $bandEntries, array $expectedFlexStyles): void
    {
        /* Arrange */
        $this->storage->save(
            'system',
            'custom-layout',
            ['name' => 'Custom Layout', 'type' => 'invoice'],
            ['header' => $bandEntries],
            ReportTemplateType::INVOICE,
        );

        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'custom-layout']);

        /* Act */
        $component->mountAction('preview');

        /* Assert */
        $action       = $component->instance()->previewAction();
        $modalContent = (string) $action->getModalContent();

        foreach ($expectedFlexStyles as $style) {
            $this->assertStringContainsString($style, $modalContent);
        }
    }

    #[Test]
    public function it_moves_a_brick_to_an_allowed_band(): void
    {
        /* Arrange */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        /* Act — header_company may live in header or group_header */
        $component->call('moveBrick', 'header', 0, 'group_header');

        /* Assert */
        $bands = $component->get('data.bands');
        $this->assertSame('header_client', $bands['header'][0]['attrs']['id']);
        $this->assertSame('header_company', end($bands['group_header'])['attrs']['id']);
    }

    #[Test]
    public function it_refuses_to_move_a_brick_into_a_disallowed_band(): void
    {
        /* Arrange */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ReportBuilder::class, ['scope' => 'system', 'type' => 'invoice', 'slug' => 'default']);

        /* Act — detail_items must never land in the header band */
        $component->call('moveBrick', 'details', 0, 'header');

        /* Assert */
        $bands = $component->get('data.bands');
        $this->assertSame('detail_items', $bands['details'][0]['attrs']['id']);

        foreach ($bands['header'] as $node) {
            $this->assertNotSame('detail_items', $node['attrs']['id']);
        }
    }

    #[Test]
    public function it_clones_a_system_template_into_the_system_scope_from_the_admin_panel(): void
    {
        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ReportTemplates::class)
            ->callAction('clone', data: ['name' => 'Modern Invoice'], arguments: [
                'scope' => 'system',
                'type'  => 'invoice',
                'slug'  => 'default',
            ])
            ->assertHasNoErrors();

        /* Assert */
        $clone = $this->storage->load('system', 'modern-invoice', ReportTemplateType::INVOICE);
        $this->assertNotNull($clone);
        $this->assertSame('Modern Invoice', $clone['manifest']['name']);
    }
}
