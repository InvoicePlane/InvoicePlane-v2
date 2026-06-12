<?php

namespace Modules\Core\Filament\Admin\Resources\MailQueues\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\MailQueues\MailQueueResource;

class CreateMailQueue extends CreateRecord
{
    protected static string $resource = MailQueueResource::class;
}
