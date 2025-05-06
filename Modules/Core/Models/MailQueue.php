<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\DateFormatter;

/**
 * Class MailQueue.
 *
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

    protected $fillable = [
        'mailable_id',
        'mailable_type',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'attach_pdf',
        'is_sent',
        'error',
    ];

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
    public function mailable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedCreatedAtAttribute(): string
    {
        return DateFormatter::format($this->attributes['created_at'], true);
    }

    public function getFormattedFromAttribute()
    {
        $from = json_decode($this->attributes['from']);

        return $from->email;
    }

    public function getFormattedToAttribute(): string
    {
        return implode(', ', json_decode($this->attributes['to']));
    }

    public function getFormattedCcAttribute(): string
    {
        return implode(', ', json_decode($this->attributes['cc']));
    }

    public function getFormattedBccAttribute(): string
    {
        return implode(', ', json_decode($this->attributes['bcc']));
    }

    public function getFormattedSentAttribute(): \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator
    {
        return ($this->attributes['is_sent']) ? trans('ip.yes') : trans('ip.no');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where('created_at', 'like', '%' . $keywords . '%')
                ->orWhere('from', 'like', '%' . $keywords . '%')
                ->orWhere('to', 'like', '%' . $keywords . '%')
                ->orWhere('cc', 'like', '%' . $keywords . '%')
                ->orWhere('bcc', 'like', '%' . $keywords . '%')
                ->orWhere('subject', 'like', '%' . $keywords . '%');
        }

        return $query;
    }
}
