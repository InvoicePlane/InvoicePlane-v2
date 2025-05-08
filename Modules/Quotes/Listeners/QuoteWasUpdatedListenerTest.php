<?php

namespace Modules\Quotes\Listeners;

use PHPUnit\Framework\Attributes\CoversClass;
use Modules\Core\Tests\AbstractTestCase;

#[CoversClass(\Modules\Quotes\Listeners\QuoteWasUpdatedListener::class)]
class QuoteWasUpdatedListenerTest extends AbstractTestCase {}
