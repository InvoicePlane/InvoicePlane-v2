<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\MailQueue;

/**
 * @extends Factory<MailQueue>
 */
class MailQueueFactory extends AbstractFactory
{
    protected $model = MailQueue::class;

    public function definition(): array
    {
        return [
        ];
    }
}
