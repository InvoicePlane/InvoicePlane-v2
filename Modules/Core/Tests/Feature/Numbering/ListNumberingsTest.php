<?php

namespace Modules\Core\Tests\Feature\Numbering;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Admin\Resources\Numberings\Pages\ListNumberings;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ListNumberingsTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('smoke')]
    #[Group('crud')]
    public function it_lists_numberings(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::JOB_CARD->value,
            'name'     => 'Test Numbering',
            'next_id'  => 1,
            'left_pad' => 4,
            'format'   => null,
            'prefix'   => NumberingType::JOB_CARD->prefix(),
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('numbering', [
            'numbering_id' => $numbering->numbering_id,
            'type'         => $numbering->type->value,
            'name'         => $numbering->name,
            'next_id'      => $numbering->next_id,
            'left_pad'     => $numbering->left_pad,
            'format'       => $numbering->format,
            'prefix'       => $numbering->prefix,
            'last_id'      => null,
        ]);
    }
}
