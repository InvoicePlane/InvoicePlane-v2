<?php

namespace Modules\Activity\Models;

use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use App\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog.
 *
 * @property int         $id
 * @property int         $audit_id
 * @property string      $audit_type
 * @property string      $activity
 * @property string|null $info
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_log';

    protected $fillable = [
        'audit_id',
        'audit_type',
        'activity',
        'info',
    ];

    public function audit(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function getFormattedActivityAttribute(): array|string|\Illuminate\Contracts\Translation\Translator
    {
        if ($this->audit) {
            switch ($this->audit_type) {
                case Quote::class:

                    switch ($this->activity) {
                        case 'public.viewed':
                            return trans('ip.activity_quote_viewed', ['number' => $this->audit->number, 'link' => route('quotes.edit', [$this->audit->id])]);
                            break;

                        case 'public.approved':
                            return trans('ip.activity_quote_approved', ['number' => $this->audit->number, 'link' => route('quotes.edit', [$this->audit->id])]);
                            break;

                        case 'public.rejected':
                            return trans('ip.activity_quote_rejected', ['number' => $this->audit->number, 'link' => route('quotes.edit', [$this->audit->id])]);
                            break;
                    }

                    break;

                case Invoice::class:

                    switch ($this->activity) {
                        case 'public.viewed':
                            return trans('ip.activity_invoice_viewed', ['number' => $this->audit->number, 'link' => route('invoices.edit', [$this->audit->id])]);
                            break;
                        case 'public.paid':
                            return trans('ip.activity_invoice_paid', ['number' => $this->audit->number, 'link' => route('invoices.edit', [$this->audit->id])]);
                            break;
                    }

                    break;
            }
        }

        return '';
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return DateFormatter::format($this->created_at, true);
    }
}
