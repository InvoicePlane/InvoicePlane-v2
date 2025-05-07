<?php

namespace Modules\Quotes\Services;

use Modules\Quotes\Services\QuoteService;

use Modules\Quotes\Events\QuoteWasCreated;

use Modules\Quotes\Events\QuoteWasUpdated;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Quotes\Events\QuoteWasCreated;
use Modules\Quotes\Events\QuoteWasUpdated;
use Modules\Quotes\Models\Quote;

class QuoteService extends BaseService
{
    public function model(): string
    {
        return Quote::class;
    }

    public function create(array $validatedInput): Quote
    {
        $quote = new Quote(
            $validatedInput
        );

        // #40: Make Quote items and attach them to the quote!
        $quote->save();

        event(new QuoteWasCreated($quote));

        return $quote;
    }

    public function update(array $validatedInput, $quoteToUpdate): Model
    {
        $quoteToUpdate->fill($validatedInput);

        // #40: Make Quote items and attach them to the quote!
        $quoteToUpdate->save();

        event(new QuoteWasUpdated($quoteToUpdate));

        return $quoteToUpdate;
    }
}
