<?php

namespace Modules\Clients\Database\Seeders;

use LogicException;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;

class RelationsSeeder extends AbstractSeeder
{
    protected string $label = 'Relations';

    protected int $defaultCount = 15; // Example: divisible by 3 for even distribution

    public function run($company = null, $count = null): void
    {
        $this->companyId = $company ?? $this->companyId;
        $this->count     = $count ?? $this->defaultCount;

        $types = [
            RelationType::CUSTOMER,
            RelationType::PROSPECT,
            RelationType::VENDOR,
        ];

        $perType   = (int) floor($this->count / count($types));
        $remaining = $this->count - ($perType * count($types));

        foreach ($types as $type) {
            for ($i = 0; $i < $perType; $i++) {
                $this->createRelationWithDependencies($type);
            }
        }
        // Distribute any remaining
        for ($i = 0; $i < $remaining; $i++) {
            $this->createRelationWithDependencies($types[$i]);
        }
    }

    protected function buildOne(): void
    {
        throw new LogicException('Use buildMany for explicit type distribution.');
    }

    protected function createRelationWithDependencies($type): void
    {
        $relation = Relation::factory()
            ->state([
                'company_id'    => $this->companyId,
                'relation_type' => $type->value,
            ])
            ->create();

        Contact::factory()
            ->state([
                'company_id'  => $this->companyId,
                'relation_id' => $relation->id,
            ])
            ->create();

        Address::factory()
            ->state([
                'company_id'       => $this->companyId,
                'addressable_id'   => $relation->id,
                'addressable_type' => Relation::class,
            ])
            ->create();
    }
}
