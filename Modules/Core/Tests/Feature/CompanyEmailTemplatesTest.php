<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Filament\Company\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EmailTemplateResource::class)]
class CompanyEmailTemplatesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_email_templates(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create(['title' => 'Inv Sent']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee('Inv Sent');

        $this->assertDatabaseHas('email_templates', ['id' => $template->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_does_not_show_email_templates_from_another_company(): void
    {
        /* Arrange */
        $other = EmailTemplate::factory()->for(Company::factory()->create())->create(['title' => 'Other Co Template']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertDontSee('Other Co Template');
        $component->assertCanNotSeeTableRecords([$other]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'title'      => 'Quote Sent',
            'type'       => EmailTemplateType::TEXT->value,
            'subject'    => 'Your quote {{ quote.number }}',
            'body'       => 'Please find your quote attached.',
            'from_name'  => 'Acme Corp',
            'from_email' => 'billing@acme.test',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', [
            'title'      => 'Quote Sent',
            'company_id' => $this->company->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_without_required_title(): void
    {
        /* Arrange */
        $payload = [
            'type' => EmailTemplateType::TEXT->value,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['title']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template_through_a_modal(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create(['title' => 'Old Title']);
        $payload  = ['title' => 'Updated Title'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('edit')->table($template), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', [
            'id'    => $template->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_email_template(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create(['title' => 'To Delete']);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('delete')->table($template))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
    # endregion
}
