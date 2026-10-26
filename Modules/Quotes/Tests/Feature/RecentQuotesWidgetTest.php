<?php

namespace Modules\Quotes\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Filament\Company\Widgets\RecentQuotesWidget;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentQuotesWidget::class)]
class RecentQuotesWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_quotes_index_page(): void
    {
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->prospect()->create();

        Quote::factory()
            ->for($this->company)
            ->create([
                'quote_number' => 'Q-0001',
                'prospect_id'  => $prospect->id,
                'user_id'      => $this->user->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentQuotesWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(QuoteResource::getUrl('index'), false);
    }
}
