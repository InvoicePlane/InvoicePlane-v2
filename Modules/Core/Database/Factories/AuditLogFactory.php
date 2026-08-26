<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;

class AuditLogFactory extends AbstractFactory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'audit_id'   => User::query()->inRandomOrder()->first()->id,
            'audit_type' => User::class,
            'activity'   => fake()->word,
            'info'       => fake()->sentence(),
        ];
    }
}
