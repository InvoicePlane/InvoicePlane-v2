<?php

namespace Modules\Quotes\Tests\Feature;

use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Mail\QuoteMailable;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QuoteMailable::class)]
class QuoteMailableTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('crud')]
    public function it_includes_a_link_to_the_guest_signing_view_in_the_email_body(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $rendered = (new QuoteMailable($quote, 'Subject line', 'Body text'))->render();

        /* Assert */
        $this->assertStringContainsString(route('quotes.guest.show', $quote), $rendered);
        $this->assertStringContainsString('Body text', $rendered);
    }
}
