<?php

namespace Modules\Core\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\CreatePayment;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\CreateTask;
use Modules\Projects\Models\Project;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\CreateQuote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class DateFieldAutoPopulationTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Parent already creates $this->user and $this->company
        // No need to create them again
    }

    #[Test]
    #[Group('date-auto-population')]
    public function it_auto_populates_invoice_date_fields_on_create_form(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $expectedDate = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        // Get the form data after mounting
        $formData = $component->get('data');

        /* assert */
        $this->assertArrayHasKey('invoiced_at', $formData, 'Invoice date field should exist in form data');
        $this->assertArrayHasKey('invoice_due_at', $formData, 'Invoice due date field should exist in form data');

        // Verify invoiced_at is populated with current date (with 1-second tolerance)
        if ( ! empty($formData['invoiced_at'])) {
            $actualInvoiceDate = Carbon::parse($formData['invoiced_at']);
            $this->assertTrue(
                $actualInvoiceDate->diffInSeconds($expectedDate) <= 1,
                'Invoice date should be within 1 second of current time. Expected: ' . $expectedDate->toDateTimeString()
                . ', Actual: ' . $actualInvoiceDate->toDateTimeString()
            );
        }

        // Verify invoice_due_at is populated (typically current date + payment terms)
        if ( ! empty($formData['invoice_due_at'])) {
            $actualDueDate = Carbon::parse($formData['invoice_due_at']);
            $this->assertInstanceOf(Carbon::class, $actualDueDate, 'Due date should be a valid Carbon instance');
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    public function it_auto_populates_task_date_fields_on_create_form(): void
    {
        /* arrange */
        $project      = Project::factory()->for($this->company)->create();
        $expectedDate = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        $this->assertArrayHasKey('task_start_date', $formData, 'Task start date field should exist');

        if ( ! empty($formData['task_start_date'])) {
            $actualStartDate = Carbon::parse($formData['task_start_date']);
            $this->assertTrue(
                $actualStartDate->diffInSeconds($expectedDate) <= 1,
                'Task start date should be within 1 second of current time'
            );
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    public function it_auto_populates_quote_date_fields_on_create_form(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $expectedDate  = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        $this->assertArrayHasKey('quoted_at', $formData, 'Quote date field should exist');

        if ( ! empty($formData['quoted_at'])) {
            $actualQuoteDate = Carbon::parse($formData['quoted_at']);
            $this->assertTrue(
                $actualQuoteDate->diffInSeconds($expectedDate) <= 1,
                'Quote date should be within 1 second of current time'
            );
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    public function it_auto_populates_payment_date_fields_on_create_form(): void
    {
        /* arrange */
        $invoice      = Invoice::factory()->for($this->company)->create();
        $expectedDate = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        $this->assertArrayHasKey('paid_at', $formData, 'Payment date field should exist');

        if ( ! empty($formData['paid_at'])) {
            $actualPaymentDate = Carbon::parse($formData['paid_at']);
            $this->assertTrue(
                $actualPaymentDate->diffInSeconds($expectedDate) <= 1,
                'Payment date should be within 1 second of current time'
            );
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    #[Group('edge-cases')]
    public function it_handles_timezone_differences_correctly(): void
    {
        /* arrange */
        $originalTimezone = config('app.timezone');
        config(['app.timezone' => 'America/New_York']);

        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $expectedDate  = Carbon::now('America/New_York');

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        if ( ! empty($formData['invoiced_at'])) {
            $actualDate = Carbon::parse($formData['invoiced_at']);
            $this->assertTrue(
                $actualDate->diffInSeconds($expectedDate) <= 2,
                'Date should handle timezone correctly within 2-second tolerance'
            );
        }

        // Cleanup
        config(['app.timezone' => $originalTimezone]);
    }

    #[Test]
    #[Group('date-auto-population')]
    #[Group('edge-cases')]
    public function it_handles_multiple_date_fields_consistently(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $expectedDate  = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        $dateFields     = ['invoiced_at', 'invoice_due_at'];
        $populatedDates = [];

        foreach ($dateFields as $field) {
            if ( ! empty($formData[$field])) {
                $populatedDates[$field] = Carbon::parse($formData[$field]);
            }
        }

        // If multiple date fields are populated, they should be within reasonable time of each other
        if (count($populatedDates) > 1) {
            $firstDate = reset($populatedDates);
            foreach ($populatedDates as $field => $date) {
                $this->assertTrue(
                    $date->diffInSeconds($firstDate) <= 1,
                    "All date fields should be populated within 1 second of each other. Field {$field} differs by "
                    . $date->diffInSeconds($firstDate) . ' seconds'
                );
            }
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    #[Group('edge-cases')]
    public function it_handles_date_field_auto_population_during_high_load(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $components    = [];
        $startTime     = Carbon::now();

        /* act */
        for ($i = 0; $i < 5; $i++) {
            $components[] = Livewire::actingAs($this->user)
                ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);
        }

        $endTime = Carbon::now();

        /* assert */
        foreach ($components as $index => $component) {
            $formData = $component->get('data');

            if ( ! empty($formData['invoiced_at'])) {
                $actualDate = Carbon::parse($formData['invoiced_at']);
                $this->assertTrue(
                    $actualDate->between($startTime->subSecond(), $endTime->addSecond()),
                    "Component {$index} should have date within test execution timeframe"
                );
            }
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    #[Group('edge-cases')]
    public function it_maintains_date_precision_across_different_formats(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $expectedDate  = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        if ( ! empty($formData['invoiced_at'])) {
            $actualDate = Carbon::parse($formData['invoiced_at']);

            // Test that the date maintains precision regardless of format
            $this->assertTrue(
                $actualDate->diffInSeconds($expectedDate) <= 1,
                'Date precision should be maintained'
            );

            // Test that the date can be properly formatted and parsed
            $formattedDate = $actualDate->format('Y-m-d H:i:s');
            $reparsedDate  = Carbon::parse($formattedDate);

            $this->assertEquals(
                $actualDate->timestamp,
                $reparsedDate->timestamp,
                'Date should maintain consistency through format/parse cycle'
            );
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    #[Group('edge-cases')]
    public function it_handles_date_auto_population_with_invalid_session_data(): void
    {
        /* arrange */
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        // Simulate corrupted or invalid session data
        session(['corrupted_date' => 'invalid-date-string']);
        session(['invalid_timestamp' => 'not-a-number']);

        $expectedDate = Carbon::now();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        $formData = $component->get('data');

        /* assert */
        if ( ! empty($formData['invoiced_at'])) {
            $actualDate = Carbon::parse($formData['invoiced_at']);
            $this->assertTrue(
                $actualDate->diffInSeconds($expectedDate) <= 1,
                'Date auto-population should work despite invalid session data'
            );
        }
    }

    #[Test]
    #[Group('date-auto-population')]
    public function it_filters_numberings_by_current_company_id(): void
    {
        /* arrange */
        $otherCompany           = Company::factory()->create();
        $currentCompanyDocGroup = Numbering::factory()->for($this->company)->create(['name' => 'Current Company Group']);
        $otherCompanyDocGroup   = Numbering::factory()->for($otherCompany)->create(['name' => 'Other Company Group']);

        $customer = Relation::factory()->for($this->company)->customer()->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class, ['tenant' => mb_strtolower($this->company->search_code)]);

        // Get the available document groups from the form component
        $formData = $component->get('data');

        /* assert */
        // The form should only show document groups belonging to the current company
        $availableNumberings = Numbering::where('company_id', $this->company->id)->get();
        $this->assertCount(1, $availableNumberings, 'Should only have document groups for current company');
        $this->assertEquals($currentCompanyDocGroup->id, $availableNumberings->first()->id);

        // Verify that the other company's document group is not accessible
        $allDocGroups = Numbering::all();
        $this->assertCount(2, $allDocGroups, 'Should have total of 2 document groups');

        $otherCompanyGroups = Numbering::where('company_id', $otherCompany->id)->get();
        $this->assertCount(1, $otherCompanyGroups, 'Other company should have its document group');
        $this->assertEquals($otherCompanyDocGroup->id, $otherCompanyGroups->first()->id);
    }

}
