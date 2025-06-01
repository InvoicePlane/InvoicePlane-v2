<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\MailQueue;

/**
 * @extends Factory<\Modules\Core\Models\MailQueue>
 */
class MailQueueFactory extends Factory
{
    protected $model = MailQueue::class;

    public function definition(): array
    {
        return [
        ];
    }
}
