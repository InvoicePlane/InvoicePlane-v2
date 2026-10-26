<?php

namespace Modules\Core\Tests\Feature\Admin;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions\AssignEmailTemplateBulkAction;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyEmailTemplateBulkActionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_email_template_to_multiple_companies(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(2)->create();
        $template  = EmailTemplate::factory()->create();
        $ids       = $companies->pluck('id')->all();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($ids)
            ->callAction(TestAction::make('assignEmailTemplate')->table()->bulk(), ['email_template_id' => $template->id])
            ->assertHasNoFormErrors();

        /* Assert */
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_email_template', [
                'company_id'        => $company->id,
                'email_template_id' => $template->id,
            ]);
        }
    }

    #[Test]
    public function it_does_not_duplicate_the_pivot_row_when_the_template_is_already_assigned(): void
    {
        /* Arrange */
        $company  = Company::factory()->create();
        $template = EmailTemplate::factory()->create();
        $company->emailTemplates()->attach($template->id);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignEmailTemplate')->table()->bulk(), ['email_template_id' => $template->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_email_template', 1);
    }

    #[Test]
    public function it_loads_a_template_owned_by_another_company_through_the_pivot(): void
    {
        /* Arrange */
        $ownerCompany  = Company::factory()->create();
        $targetCompany = Company::factory()->create();
        $template      = EmailTemplate::factory()->for($ownerCompany)->create();
        $targetCompany->emailTemplates()->attach($template->id);

        /* Act */
        $loaded = $targetCompany->emailTemplates()->find($template->id);

        /* Assert */
        $this->assertNotNull($loaded);
        $this->assertSame($template->id, $loaded->id);
    }

    #[Test]
    public function it_shows_humanized_titles_disambiguated_by_owning_company(): void
    {
        /* Arrange */
        $companyOne = Company::factory()->create(['name' => 'Acme Corp']);
        $companyTwo = Company::factory()->create(['name' => 'Other Corp']);
        $templateOne = EmailTemplate::factory()->for($companyOne)->create(['title' => 'invoice_sent']);
        $templateTwo = EmailTemplate::factory()->for($companyTwo)->create(['title' => 'invoice_sent']);

        /* Act */
        $options = AssignEmailTemplateBulkAction::getEmailTemplateOptions();

        /* Assert */
        $this->assertSame('Invoice Sent (Acme Corp)', $options[$templateOne->id]);
        $this->assertSame('Invoice Sent (Other Corp)', $options[$templateTwo->id]);
        $this->assertNotSame($options[$templateOne->id], $options[$templateTwo->id]);
    }

    #[Test]
    public function it_requires_an_email_template_to_be_selected(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignEmailTemplate')->table()->bulk(), ['email_template_id' => null])
            ->assertHasFormErrors(['email_template_id' => 'required']);

        $this->assertDatabaseCount('company_email_template', 0);
    }
}
