<?php

namespace Modules\Core\Observers;

use Modules\Core\Models\Attachment;

class AttachmentObserver
{
    /**
     * Handle the AttachmentObserver "created" event.
     */
    public function created(Attachment $attachmentobserver): void {}

    /**
     * Handle the Attachment "updated" event.
     */
    public function updated(Attachment $attachmentobserver): void {}

    /**
     * Handle the Attachment "deleted" event.
     */
    public function deleted(Attachment $attachmentobserver): void {}

    /**
     * Handle the Attachment "restored" event.
     */
    public function restored(Attachment $attachmentobserver): void {}

    /**
     * Handle the Attachment "force deleted" event.
     */
    public function forceDeleted(Attachment $attachmentobserver): void {}

    /*public static function boot(): void
    {
        parent::boot();

        static::creating(function ($attachment): void {
            event(new AttachmentCreating($attachment));
        });

        static::deleted(function ($attachment): void {
            event(new AttachmentDeleted($attachment));
        });
    }*/
}
