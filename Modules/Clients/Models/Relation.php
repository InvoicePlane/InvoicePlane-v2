<?php

namespace Modules\Clients\Models;

use Modules\Clients\Enums\RelationType;

use Modules\Projects\Models\Task;

use Modules\Clients\Database\Factories\RelationFactory;

use Modules\Invoices\Models\RecurringInvoice;

use Modules\Core\Support\Statuses\InvoiceStatuses;

use Modules\Invoices\Models\Invoice;

use Modules\Expenses\Models\Expense;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Models\Note;

use Modules\Clients\Models\Contact;

use Modules\Quotes\Models\Quote;

use Modules\Core\Models\Address;

use Modules\Core\Models\Communication;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Projects\Models\Project;

use Modules\Clients\Enums\RelationStatus;

use Modules\Core\Models\Addressable;

use Modules\Core\Support\Results\Invoices;

use Modules\Clients\Models\Relation;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int                           $id
 * @property int                           $primary_contact_id
 * @property string                        $relation_type
 * @property string                        $relation_status
 * @property string                        $relation_number
 * @property string                        $company_name
 * @property string                        $trading_name
 * @property string                        $id_number
 * @property string                        $coc_number
 * @property string                        $vat_number
 * @property Carbon                        $registered_at
 * @property mixed                         $created_at
 * @property mixed                         $updated_at
 * @property Invoice[]                     $invoices
 * @property Quote[]                       $quotes
 * @property Project[]                     $projects
 * @property Contact                       $contact
 * @property string|null                   $currency_code
 * @property string|null                   $language
 * @property Collection|Contact[]          $contacts
 * @property Collection|Expense[]          $expenses
 * @property Collection|Invoice[]          $invoices
 * @property Collection|Quote[]            $quotes
 * @property Collection|RecurringInvoice[] $recurring_invoices
 * @property Collection|User[]             $users
 * @property Task[]                        $tasks
 */
class Relation extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $table = 'relations';

    protected $casts = [
        'relation_type'   => RelationType::class,
        'relation_status' => RelationStatus::class,
    ];

    protected $guarded = [];

    /**
     * Observer.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($client): void {
            event(new ClientCreating($client));
        });

        static::created(function ($client): void {
            event(new ClientCreated($client));
        });

        static::saving(function ($client): void {
            event(new ClientSaving($client));
        });

        static::deleted(function ($client): void {
            event(new ClientDeleted($client));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    public static function firstOrCreateByUniqueName($uniqueName)
    {
        $client = self::firstOrNew([
            'unique_name' => $uniqueName,
        ]);

        if ( ! $client->id) {
            $client->name = $uniqueName;
            $client->save();

            return self::find($client->id);
        }

        return $client;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function attachments(): void
    {
        // return $this->morphMany(Attachment, 'attachable');
    }

    public function addressables(): MorphMany
    {
        return $this->morphMany(Addressable::class, 'addressable');
    }

    public function addresses(): HasManyThrough
    {
        return $this->hasManyThrough(
            Address::class,
            Addressable::class,
            'addressable_id',
            'id',
            'id',
            'address_id'
        );
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicationable');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'relation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function merchant()
    {
        return $this->hasOne('Modules\Core\Models\PaymentTypeClient');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getAttachmentPathAttribute()
    {
        return attachment_path('clients/' . $this->id);
    }

    public function getAttachmentPermissionOptionsAttribute()
    {
        return ['0' => trans('ip.not_visible')];
    }

    public function getFormattedBalanceAttribute()
    {
        return CurrencyFormatter::format($this->balance, $this->currency);
    }

    public function getFormattedPaidAttribute()
    {
        return CurrencyFormatter::format($this->paid, $this->currency);
    }

    public function getFormattedTotalAttribute()
    {
        return CurrencyFormatter::format($this->total, $this->currency);
    }

    public function getFormattedAddressAttribute()
    {
        return nl2br(formatAddress($this));
    }

    public function getClientEmailAttribute()
    {
        return $this->email;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeStatus(Builder $query, $status): Builder
    {
        switch ($status) {
            case 'active':
                return $query->where('client_active', true);
            case 'inactive':
                return $query->where('client_active', false);
            default:
                return $query;
        }
    }

    public function scopeGetSelect()
    {
        return self::select(
            'customers.*',
            DB::raw('(' . $this->getBalanceSql() . ') as balance'),
            DB::raw('(' . $this->getPaidSql() . ') AS paid'),
            DB::raw('(' . $this->getTotalSql() . ') AS total')
        );
    }

    /*public function scopeStatus($query, $status)
    {
        if ($status == 'is_active') {
            $query->where('is_active', 1);
        } elseif ($status == 'inactive') {
            $query->where('is_active', 0);
        }

        return $query;
    }*/

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords) {
            $keywords = explode(' ', $keywords);

            foreach ($keywords as $keyword) {
                if ($keyword) {
                    $keyword = mb_strtolower($keyword);

                    $query->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name),LOWER(email),phone,fax,mobile)"), 'LIKE', "%{$keyword}%");
                }
            }
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return RelationFactory::new();
    }

    private function getBalanceSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(balance)'))->whereIn('invoice_id', function ($q): void {
            $q->select('id')
                ->from('invoices')
                ->where('invoices.customer_id', '=', DB::raw(DB::getTablePrefix() . 'customers.id'))
                ->where('invoices.invoice_status_id', '<>', DB::raw(InvoiceStatuses::getStatusId('canceled')));
        })->toSql();
    }

    private function getPaidSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(paid)'))->whereIn('invoice_id', function ($q): void {
            $q->select('id')->from('invoices')->where('invoices.customer_id', '=', DB::raw(DB::getTablePrefix() . 'customers.id'));
        })->toSql();
    }

    /*
    |--------------------------------------------------------------------------
    | Subqueries
    |--------------------------------------------------------------------------
    */
    private function getTotalSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(total)'))->whereIn('invoice_id', function ($q): void {
            $q->select('id')->from('invoices')->where('invoices.customer_id', '=', DB::raw(DB::getTablePrefix() . 'customers.id'));
        })->toSql();
    }
}
