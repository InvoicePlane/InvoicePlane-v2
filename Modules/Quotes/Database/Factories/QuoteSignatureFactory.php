<?php

namespace Modules\Quotes\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\User;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteSignature;

class QuoteSignatureFactory extends AbstractFactory
{
    protected $model = QuoteSignature::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();

        return [
            'quote_id'       => $this->resolveForeignKey(Quote::class, $companyId),
            'user_id'        => null,
            'signer_name'    => fake()->name(),
            'signature_disk' => 'local',
            'signature_path' => 'quote-signatures/' . fake()->uuid() . '.png',
            'signed_at'      => now(),
            'ip_address'     => fake()->ipv4(),
            'user_agent'     => fake()->userAgent(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id'     => $user->id,
            'signer_name' => $user->name,
        ]);
    }
}
