<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListQuotes::class)]
class QuoteNumberGenerationOnCreateTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_auto_generates_a_quote_number_on_create_when_none_is_supplied(): void
    {
        /* Arrange */
        $prospect  = Relation::factory()->for($this->company)->prospect()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::QUOTE->value,
            'prefix'   => 'QUO',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $payload = $this->basePayload($prospect->id, $numbering->id, QuoteStatus::DRAFT->value);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $quote = Quote::query()->where('company_id', $this->company->id)->latest('id')->first();
        $this->assertNotNull($quote);
        $this->assertNotNull($quote->quote_number);
        $this->assertStringStartsWith('QUO-', $quote->quote_number);

        $component->assertNotified(trans('ip.quote_created_with_number', ['number' => $quote->quote_number]));
    }

    #[Test]
    public function it_does_not_auto_populate_quote_number_for_drafts_when_the_setting_is_disabled(): void
    {
        /* Arrange */
        Setting::saveByKey('generate_quote_number_for_draft', '0');

        $prospect  = Relation::factory()->for($this->company)->prospect()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::QUOTE->value,
            'prefix'   => 'QUO',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        // quote_number intentionally omitted: with the setting disabled, the
        // form should no longer silently auto-fill it, so the still-required
        // field surfaces a validation error instead of a generated number.
        $payload = $this->basePayload($prospect->id, $numbering->id, QuoteStatus::DRAFT->value);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['quote_number' => 'required']);
        $this->assertSame(1, Numbering::query()->find($numbering->id)->next_id, 'the counter must not advance when no number was generated');
    }

    #[Test]
    public function it_still_generates_a_quote_number_for_non_draft_status_when_the_draft_setting_is_disabled(): void
    {
        /* Arrange */
        Setting::saveByKey('generate_quote_number_for_draft', '0');

        $prospect  = Relation::factory()->for($this->company)->prospect()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::QUOTE->value,
            'prefix'   => 'QUO',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        // quote_status is set via a real Livewire property update (not fillForm,
        // which bypasses afterStateUpdated hooks) so the form's reactive
        // regeneration of quote_number on status change actually fires --
        // mirroring a user picking "Approved" interactively in the modal.
        $payload = $this->basePayload($prospect->id, $numbering->id, QuoteStatus::APPROVED->value);
        unset($payload['quote_status']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->set('mountedActions.0.data.quote_status', QuoteStatus::APPROVED->value)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $quote = Quote::query()->where('company_id', $this->company->id)->latest('id')->first();
        $this->assertNotNull($quote);
        $this->assertNotNull($quote->quote_number);
        $this->assertStringStartsWith('QUO-', $quote->quote_number);
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(int $prospectId, int $numberingId, string $status): array
    {
        return [
            'prospect_id'            => $prospectId,
            'numbering_id'           => $numberingId,
            'quote_status'           => $status,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0,
            'quote_discount_percent' => 0,
            'quote_tax_total'        => 0,
            'quote_item_subtotal'    => 0,
            'quote_total'            => 0,
            'quoteItems'             => [],
        ];
    }
}
