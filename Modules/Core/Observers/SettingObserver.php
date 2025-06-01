<?php

namespace Modules\Core\Observers;

use Modules\Core\Models\Setting;

class SettingObserver
{
    /**
     * Handle the Setting "created" event.
     */
    public function created(Setting $settingobserver): void {}

    /**
     * Handle the Setting "updated" event.
     */
    public function updated(Setting $settingobserver): void {}

    /**
     * Handle the Setting "deleted" event.
     */
    public function deleted(Setting $settingobserver): void {}

    /**
     * Handle the Setting "restored" event.
     */
    public function restored(Setting $settingobserver): void {}

    /**
     * Handle the Setting "force deleted" event.
     */
    public function forceDeleted(Setting $settingobserver): void {}

    /*public static function boot(): void
    {
        parent::boot();

        static::saving(function ($setting): void {
            event(new SettingSaving($setting));
        });
    }*/
}
