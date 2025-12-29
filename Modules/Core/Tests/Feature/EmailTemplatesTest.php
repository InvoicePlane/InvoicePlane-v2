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
        /* arrange */
        $template = EmailTemplate::factory()->for($this->company)->create(['subject' => 'Test Email']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('email_templates', $template->toArray());
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template_through_a_modal(): void
    {
        /* arrange */
        $payload = [
            'title'      => 'Test Email',
            'subject'    => 'Welcome',
            'body'       => '',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_email_template_through_a_modal_without_required_title(): void
    {
        /* arrange */
        $payload = [
            'subject'    => 'Welcome',
            'body'       => '',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_through_a_modal_without_required_type(): void
    {
        /* arrange */
        $payload = [
            'title'      => 'Welcome',
            'subject'    => 'Test Email',
            'body'       => '',
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template_through_a_modal(): void
    {
        /* arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Old Title',
            'subject' => 'Old Subject',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        $payload = ['subject' => 'Updated Subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('edit')->table($template), $payload)
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
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
            'body'       => '',
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
        /* arrange */
        $payload = [
            'subject'    => 'Welcome',
            'body'       => '',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_without_required_type(): void
    {
        /* arrange */
        $payload = [
            'title'      => 'Welcome',
            'subject'    => 'Test Email',
            'body'       => '',
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template(): void
    {
        /* arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Old Title',
            'subject' => 'Old Subject',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        $payload = ['subject' => 'Updated Subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditEmailTemplate::class, ['record' => $template->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_email_template(): void
    {
        /* arrange */
        $template = EmailTemplate::factory()->for($this->company)->create([
            'title'   => 'Template to Delete',
            'subject' => 'Delete Me',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        /* act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListEmailTemplates::class)
            ->mountAction(TestAction::make('delete')->table($template))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('numberings', ['id' => $template->id]);
    }
    # endregion

    #region spicy
    # endregion
}
