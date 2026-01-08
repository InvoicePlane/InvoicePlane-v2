<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int         $id
 * @property int         $mailable_id
 * @property string      $mailable_type
 * @property string      $from
 * @property string      $to
 * @property string      $cc
 * @property string      $bcc
 * @property string      $subject
 * @property string      $body
 * @property bool        $attach_pdf
 * @property bool        $is_sent
 * @property string|null $error
 */
class MailQueue extends Model
{
    public $timestamps = false;

    protected $table = 'mail_queue';

    protected $casts = [
        'attach_pdf' => 'bool',
        'is_sent'    => 'bool',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function mailable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
}
