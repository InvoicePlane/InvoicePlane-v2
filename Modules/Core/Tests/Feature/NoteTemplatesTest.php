<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Company\Resources\NoteTemplates\NoteTemplateResource;
use Modules\Core\Filament\Company\Resources\NoteTemplates\Pages\ListNoteTemplates;
use Modules\Core\Models\Company;
use Modules\Core\Models\NoteTemplate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NoteTemplateResource::class)]
class NoteTemplatesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_note_templates(): void
    {
        /* Arrange */
        $template = NoteTemplate::factory()->for($this->company)->create([
            'template_title' => 'SEO Terms',
            'template_body'  => 'Payment due Net 30.',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee('SEO Terms');

        $this->assertDatabaseHas('note_templates', $template->toArray());
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_does_not_show_note_templates_from_another_company(): void
    {
        /* Arrange */
        $other = NoteTemplate::factory()->for(Company::factory()->create())->create([
            'template_title' => 'Other Terms',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertDontSee('Other Terms');
        $component->assertCanNotSeeTableRecords([$other]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_note_template_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'template_title' => 'Web Dev Payment Terms',
            'template_body'  => '50% deposit, 50% on delivery.',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('note_templates', array_merge($payload, [
            'company_id' => $this->company->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_note_template_without_required_title(): void
    {
        /* Arrange */
        $payload = [
            'template_body' => 'Some body text.',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['template_title']);

        $this->assertDatabaseMissing('note_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_note_template_through_a_modal(): void
    {
        /* Arrange */
        $template = NoteTemplate::factory()->for($this->company)->create([
            'template_title' => 'Old Title',
            'template_body'  => 'Old body.',
        ]);

        $payload = ['template_title' => 'Updated Title'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class)
            ->mountAction(TestAction::make('edit')->table($template), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('note_templates', array_merge($payload, [
            'id' => $template->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_note_template(): void
    {
        /* Arrange */
        $template = NoteTemplate::factory()->for($this->company)->create([
            'template_title' => 'Template to Delete',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListNoteTemplates::class)
            ->mountAction(TestAction::make('delete')->table($template))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('note_templates', ['id' => $template->id]);
    }
    # endregion
}
