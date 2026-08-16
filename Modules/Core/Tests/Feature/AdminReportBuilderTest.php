<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Filament\Admin\Pages\ReportBuilder;
use Modules\Core\Filament\Admin\Pages\ReportTemplates;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
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
