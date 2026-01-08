<?php

namespace Modules\Core\Filament\Admin\Resources\MailQueues\Schemas;

use Filament\Schemas\Schema;

class MailQueueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }
}
