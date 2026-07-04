<?php

namespace Modules\Clients\Exceptions;

use RuntimeException;

class RelationHasLinkedRecordsException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: trans('ip.cannot_delete_client_has_linked_records'));
    }
}
