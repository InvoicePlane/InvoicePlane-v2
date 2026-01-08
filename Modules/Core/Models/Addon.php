<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Migrations;

/**
 * @property int         $id
 * @property bool        $is_enabled
 * @property string      $name
 * @property string      $author_name
 * @property string      $author_url
 * @property string|null $navigation_menu
 * @property string|null $system_menu
 * @property string|null $navigation_reports
 * @property string      $path
 */
class Addon extends Model
{
    public $timestamps = false;

    protected $table = 'addons';

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $guarded = ['id'];

    public function getHasPendingMigrationsAttribute(): bool
    {
        return false;
        /*$migrations = new Migrations();

        return (bool) ($migrations->getPendingMigrations(addon_path($this->path . '/Migrations')));*/
    }
}
