<?php

namespace Modules\Core\Filament\Admin\Resources\MailQueues\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\MailQueues\MailQueueResource;

class EditMailQueue extends EditRecord
{
    protected static string $resource = MailQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
