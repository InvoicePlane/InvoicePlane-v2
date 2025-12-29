<?php

namespace Modules\Quotes\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Quotes\Models\Quote;

class QuotesSeeder extends AbstractSeeder
{
    protected string $label = 'Quotes';

    protected int $defaultCount = 10;

    protected function buildOne(): void
    {
        $prospect      = $this->findOrCreateProspect($this->companyId);
        $documentGroup = $this->findOrCreateNumbering($this->companyId);
        $user          = $this->findOrCreateUser($this->companyId);

        Quote::factory()
            ->state([
                'company_id'   => $this->companyId,
                'prospect_id'  => $prospect->id,
                'numbering_id' => $documentGroup->id,
                'user_id'      => $user->id,
            ])
            ->create();
    }
}
