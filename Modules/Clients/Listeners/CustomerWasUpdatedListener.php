<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Models\Relation;

class CustomerWasUpdatedListener
{
    public function __construct() {}

    public function handle(Relation $event): void {}
}
