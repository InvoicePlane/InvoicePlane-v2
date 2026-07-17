<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Admin\Resources\Numberings\Pages\CreateNumbering;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class NumberingFormatBuilderTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_inserts_a_token_into_the_format_field_when_a_button_is_clicked(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateNumbering::class)
            ->fillForm(['format' => '{{prefix}}-'])
            ->mountAction('insert_format_number')
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $component->assertFormSet(['format' => '{{prefix}}-{{number}}']);
    }

    #[Test]
    public function it_inserts_a_token_into_the_group_identifier_format_field_independently_of_the_format_field(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateNumbering::class)
            ->fillForm(['format' => '{{prefix}}', 'group_identifier_format' => '{{year}}-'])
            ->mountAction('insert_group_identifier_format_number')
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $component->assertFormSet([
            'format'                   => '{{prefix}}',
            'group_identifier_format'  => '{{year}}-{{number}}',
        ]);
    }

    #[Test]
    public function it_uses_inv_as_the_prefix_placeholder_example_instead_of_job(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateNumbering::class);

        /* Assert */
        $component->assertSuccessful();
        $html = $component->html();
        $this->assertStringNotContainsString('placeholder="JOB"', $html);
    }

    #[Test]
    public function it_creates_a_numbering_with_a_group_identifier_format(): void
    {
        /* Arrange */
        $payload = [
            'company_id'               => $this->company->id,
            'type'                     => NumberingType::INVOICE->value,
            'name'                     => '::numbering_name::',
            'next_id'                  => 1,
            'left_pad'                 => 4,
            'prefix'                   => 'INV',
            'format'                   => '{{prefix}}-{{number}}',
            'group_identifier_format'  => '{{prefix}}-{{year}}-{{number}}',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateNumbering::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('numbering', [
            'name'                    => $payload['name'],
            'group_identifier_format' => $payload['group_identifier_format'],
        ]);
    }
}
