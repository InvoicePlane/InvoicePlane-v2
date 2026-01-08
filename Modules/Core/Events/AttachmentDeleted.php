<?php

namespace Modules\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use League\CommonMark\Event\AbstractEvent;
use Modules\Core\Models\Attachment;

class AttachmentDeleted extends AbstractEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(Attachment $attachment) {}
}
