<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Models\Relation;

class CustomerWasCreatedListener
{
    public function __construct() {}

    public function handle(Relation $event): void {}
}
