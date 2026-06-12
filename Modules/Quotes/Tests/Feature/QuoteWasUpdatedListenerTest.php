<?php

namespace Modules\Quotes\Listeners;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Modules\Quotes\Listeners\QuoteWasUpdatedListener::class)]
class QuoteWasUpdatedListenerTest extends AbstractTestCase {}
