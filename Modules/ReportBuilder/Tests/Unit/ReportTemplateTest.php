<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ReportTemplateTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Company $company */
        $company = Company::factory()->create(['name' => 'Test Company']);
        $this->company = $company;
    }

    #[Test]
    #[Group('unit')]
    public function it_can_create_a_report_template(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Professional Invoice',
            'slug'          => 'professional_invoice',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        /* assert */
        $this->assertDatabaseHas('report_templates', [
            'company_id'    => $this->company->id,
            'name'          => 'Professional Invoice',
            'slug'          => 'professional_invoice',
            'template_type' => 'invoice',
        ]);
        $this->assertEquals('Professional Invoice', $template->name);
        $this->assertEquals('professional_invoice', $template->slug);
        $this->assertFalse($template->is_system);
        $this->assertTrue($template->is_active);
    }

    #[Test]
    #[Group('unit')]
    public function it_casts_boolean_fields_correctly(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'System Template',
            'slug'          => 'system_template',
            'template_type' => 'invoice',
            'is_system'     => true,
            'is_active'     => false,
        ]);

        /* assert */
        $this->assertTrue($template->is_system);
        $this->assertFalse($template->is_active);
        $this->assertIsBool($template->is_system);
        $this->assertIsBool($template->is_active);
    }

    #[Test]
    #[Group('unit')]
    public function it_belongs_to_a_company(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Test Template',
            'slug'          => 'test_template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        /* assert */
        $this->assertInstanceOf(Company::class, $template->company);
        $this->assertEquals($this->company->id, $template->company->id);
    }

    #[Test]
    #[Group('unit')]
    public function is_cloneable_returns_true_when_active(): void
    {
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Active Template',
            'slug'          => 'active_template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $this->assertTrue($template->isCloneable());
    }

    #[Test]
    #[Group('unit')]
    public function is_cloneable_returns_false_when_inactive(): void
    {
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Inactive Template',
            'slug'          => 'inactive_template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => false,
        ]);

        $this->assertFalse($template->isCloneable());
    }

    #[Test]
    #[Group('unit')]
    public function is_system_returns_true_for_system_templates(): void
    {
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'System Template',
            'slug'          => 'system_template',
            'template_type' => 'invoice',
            'is_system'     => true,
            'is_active'     => true,
        ]);

        $this->assertTrue($template->isSystem());
    }

    #[Test]
    #[Group('unit')]
    public function is_system_returns_false_for_user_templates(): void
    {
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'User Template',
            'slug'          => 'user_template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $this->assertFalse($template->isSystem());
    }

    #[Test]
    #[Group('unit')]
    public function get_file_path_returns_correct_path(): void
    {
        $template = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Test Template',
            'slug'          => 'test_template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $expectedPath = "{$this->company->id}/test_template.json";
        $this->assertEquals($expectedPath, $template->getFilePath());
    }

    #[Test]
    #[Group('unit')]
    public function slug_must_be_unique_within_company(): void
    {
        ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Template 1',
            'slug'          => 'unique_slug',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Template 2',
            'slug'          => 'unique_slug',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);
    }

    #[Test]
    #[Group('unit')]
    public function same_slug_can_exist_in_different_companies(): void
    {
        $company2 = Company::factory()->create(['name' => 'Company 2']);

        $template1 = ReportTemplate::create([
            'company_id'    => $this->company->id,
            'name'          => 'Template 1',
            'slug'          => 'shared_slug',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $template2 = ReportTemplate::create([
            'company_id'    => $company2->id,
            'name'          => 'Template 2',
            'slug'          => 'shared_slug',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $this->assertEquals('shared_slug', $template1->slug);
        $this->assertEquals('shared_slug', $template2->slug);
        $this->assertNotEquals($template1->company_id, $template2->company_id);
    }
}
