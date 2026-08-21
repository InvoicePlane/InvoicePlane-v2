<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListEmailTemplates::class)]
class EmailTemplatesTest extends AbstractAdminPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['subject' => 'Test Email']
     */
    public function it_lists_email_templates(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create(['subject' => 'Test Email']);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class);

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('email_templates', $template->toArray());
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'title'      => 'Test Email',
            'subject'    => 'Welcome',
            'body'       => 'This is the email body content.',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_email_template_through_a_modal_without_required_title(): void
    {
        /* Arrange */
        $payload = [
            'subject'    => 'Welcome',
            'body'       => 'This is the email body content.',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_through_a_modal_without_required_type(): void
    {
        /* Arrange */
        $payload = [
            'title'      => 'Welcome',
            'subject'    => 'Test Email',
            'body'       => 'This is the email body content.',
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template_through_a_modal(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Old Title',
            'subject' => 'Old Subject',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        $payload = ['subject' => 'Updated Subject'];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('edit')->table($template), $payload)
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }
    #endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "title": "Test Email",
     *   "subject": "Welcome",
     *   "body": "",
     *   "type": "text",
     *   "from_name": "Acme Support",
     *   "from_email": "support@acme.com"
     * }
     */
    public function it_creates_an_email_template(): void
    {
        $payload = [
            'title'      => 'Test Email',
            'subject'    => 'Welcome',
            'body'       => 'This is the email body content.',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertSuccessful()->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', array_merge(
            $payload,
            ['company_id' => $this->company->getKey()]
        ));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_email_template_without_required_title(): void
    {
        /* Arrange */
        $payload = [
            'subject'    => 'Welcome',
            'body'       => 'This is the email body content.',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_without_required_type(): void
    {
        /* Arrange */
        $payload = [
            'title'      => 'Welcome',
            'subject'    => 'Test Email',
            'body'       => 'This is the email body content.',
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Old Title',
            'subject' => 'Old Subject',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        $payload = ['subject' => 'Updated Subject'];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditEmailTemplate::class, ['record' => $template->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_email_template(): void
    {
        /* Arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Template to Delete',
            'subject' => 'Delete Me',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('delete')->table($template))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
    # endregion

    #region spicy
    # endregion
}
