<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EmailTemplateResource::class)]
class EmailTemplatesTest extends AbstractAdminPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['subject' => 'Test Email']
     */
    #[Group('crud')]
    public function it_lists_email_templates(): void
    {
        /* arrange */
        $template = EmailTemplate::factory()->create(['subject' => 'Test Email']);

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
    public function it_creates_an_email_template_trough_a_modal(): void
    {
        /* arrange */
        $company = Company::factory()->create();
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

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_trough_a_modal_without_required_title(): void
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

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_trough_a_modal_without_required_type(): void
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

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company = Company::factory()->create();
        $payload = [
            'company_id' => $company->id,
            'subject'    => 'Welcome',
            'body'       => 'Hello world',
            'type'       => EmailTemplateType::BOOLEAN->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertSuccessful()->assertHasNoFormErrors();
        $this->assertDatabaseHas('email_templates', [
            'subject' => 'Welcome',
            'body'    => 'Hello world',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_email_template_without_subject(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['body' => 'Missing subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateEmailTemplate::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $template = EmailTemplate::factory()->create(['subject' => 'Old Subject']);

        $payload = ['subject' => 'Updated Subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditEmailTemplate::class, ['record' => $template->id])->fillForm($payload)->call('save');

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
        $this->markTestIncomplete();

        /* arrange */

        $template = EmailTemplate::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListEmailTemplates::class)->callTableAction('delete', $template);

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_list_email_templates_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a template with a different tenant
        $otherTemplate = EmailTemplate::factory()->create([
            'subject' => 'Other Tenant Template',
            'title'   => 'Other Template',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        // Try to access the other tenant's templates
        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class);

        // Should not see the other tenant's template
        $response->assertDontSee($otherTemplate->title);
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_create_email_template_for_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a different company/tenant
        $otherCompany = Company::factory()->create();

        // Try to create a template for the other tenant
        $payload = [
            'title'      => 'Test Template',
            'subject'    => 'Test Subject',
            'body'       => 'Test Body',
            'type'       => EmailTemplateType::TEXT->value,
            'company_id' => $otherCompany->id,
            'from_name'  => 'Test',
            'from_email' => 'test@example.com',
        ];

        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        // Should not be able to create for another tenant
        $response->assertForbidden();
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_update_email_template_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a template with a different tenant
        $otherTemplate = EmailTemplate::factory()->create([
            'subject' => 'Other Tenant Template',
            'title'   => 'Other Template',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        // Try to update the other tenant's template
        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('edit', ['record' => $otherTemplate->id])
            ->fillForm(['title' => 'Updated Title'])
            ->callMountedAction();

        // Should be forbidden or not found
        $response->assertStatus(404);
    }

    #[Test]
    #[Group('crud')]
    public function it_cannot_delete_email_template_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a template with a different tenant
        $otherTemplate = EmailTemplate::factory()->create([
            'subject' => 'Other Tenant Template',
            'title'   => 'Other Template',
            'type'    => EmailTemplateType::TEXT->value,
        ]);

        // Try to delete the other tenant's template
        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->callAction('delete', $otherTemplate);

        // Should be forbidden or not found
        $response->assertStatus(404);
        $this->assertDatabaseHas('email_templates', ['id' => $otherTemplate->id]);
    }
    # endregion
}
