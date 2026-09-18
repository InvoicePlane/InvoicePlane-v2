<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Admin\Resources\Numberings\Pages\ListNumberings;
use Modules\Core\Filament\Admin\Resources\Numberings\Schemas\NumberingForm;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumberingForm::class)]
class NumberingPrefixAutofillTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_prefills_the_prefix_with_the_selected_types_default_prefix_when_creating(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->mountAction('create')
            ->set('mountedActions.0.data.type', NumberingType::QUOTE->value);

        /* Assert */
        $component->assertSet('mountedActions.0.data.prefix', NumberingType::QUOTE->prefix());
    }

    #[Test]
    public function it_does_not_overwrite_an_already_chosen_prefix_when_the_type_changes(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->mountAction('create')
            ->set('mountedActions.0.data.prefix', 'CUSTOM')
            ->set('mountedActions.0.data.type', NumberingType::QUOTE->value);

        /* Assert */
        $component->assertSet('mountedActions.0.data.prefix', 'CUSTOM');
    }

    #[Test]
    public function it_prefills_a_different_prefix_when_the_type_selection_changes_again(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->mountAction('create')
            ->set('mountedActions.0.data.type', NumberingType::EXPENSE->value);

        /* Assert */
        $component->assertSet('mountedActions.0.data.prefix', NumberingType::EXPENSE->prefix());
    }

    #[Test]
    public function it_no_longer_shows_the_misleading_job_placeholder(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->mountAction('create');

        /* Assert */
        $component->assertDontSee('JOB');
        $component->assertSee('INV');
    }
}
