<?php

namespace Modules\Core\Tests\Feature\Numbering;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class NumberingFormTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('smoke')]
    #[Group('crud')]
    public function it_lists_numberings(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 1,
            'left_pad' => 4,
            'format'   => null,
            'prefix'   => NumberingType::PROJECT->prefix(),
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('numbering', [
            'id'       => $numbering->id,
            'type'     => $numbering->type->value,
            'name'         => $numbering->name,
            'next_id'      => $numbering->next_id,
            'left_pad'     => $numbering->left_pad,
            'format'       => $numbering->format,
            'prefix'       => $numbering->prefix,
            'last_id'      => null,
        ]);
    }
    
    #[Test]
    public function it_displays_numbering_form_fields_correctly(): void
    {
        $this->markTestIncomplete();
        $response = $this->get(route('filament.resources.numberings.create'));
        $response->assertStatus(200);
        $response->assertSee('type');
        $response->assertSee('name');
        $response->assertSee('next_id');
        $response->assertSee('left_pad');
        $response->assertSee('format');
        $response->assertDontSee('last_id');
    }

    #[Test]
    public function it_prefills_prefix_and_format_on_type_change(): void
    {
        $this->markTestIncomplete();
        // Simulate selecting each type and check prefix/format
        foreach (\Modules\Core\Enums\NumberingType::cases() as $type) {
            $response = $this->post(route('filament.resources.numberings.store'), [
                'type'     => $type->value,
                'name'     => ucfirst($type->label()),
                'format'   => '{{prefix}}-{{number}}',
                'prefix'   => $type->prefix(),
                'next_id'  => 2,
                'left_pad' => 3,
            ]);
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('numbering', [
                'type'   => $type->value,
                'format' => '{{prefix}}-{{number}}',
                'prefix' => $type->prefix(),
            ]);
        }
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->markTestIncomplete();
        $response = $this->post(route('filament.resources.numberings.store'), []);
        $response->assertSessionHasErrors(['type', 'name', 'next_id', 'left_pad']);
    }

    #[Test]
    public function it_creates_numbering_with_valid_data(): void
    {
        $this->markTestIncomplete();
        $data = [
            'type'     => \Modules\Core\Enums\NumberingType::PROJECT->value,
            'name'     => 'Test Project',
            'format'   => '{{prefix}}-{{number}}',
            'prefix'   => \Modules\Core\Enums\NumberingType::PROJECT->prefix(),
            'next_id'  => 1,
            'left_pad' => 4,
        ];
        $response = $this->post(route('filament.resources.numberings.store'), $data);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('numbering', [
            'name'   => 'Test Project',
            'type'   => \Modules\Core\Enums\NumberingType::PROJECT->value,
            'format' => '{{prefix}}-{{number}}',
            'prefix' => \Modules\Core\Enums\NumberingType::PROJECT->prefix(),
        ]);
    }
}
