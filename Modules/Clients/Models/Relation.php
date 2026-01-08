<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Database\Factories\RelationFactory;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Payments\Models\Payment;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

/**
 * @property int                           $id
 * @property int                           $company_id
 * @property int|null                      $primary_contact_id
 * @property string                        $relation_type
 * @property string                        $relation_status
 * @property string                        $relation_number
 * @property string                        $company_name
 * @property string|null                   $trading_name
 * @property string|null                   $unique_name
 * @property string|null                   $id_number
 * @property string|null                   $coc_number
 * @property string|null                   $vat_number
 * @property Carbon                        $registered_at
 * @property mixed                         $created_at
 * @property mixed                         $updated_at
 * @property Invoice[]                     $invoices
 * @property Quote[]                       $quotes
 * @property Project[]                     $projects
 * @property Contact                       $contact
 * @property string|null                   $currency_code
 * @property string|null                   $language
 * @property Company                       $company
 * @property Collection|Contact[]          $contacts
 * @property Collection|Expense[]          $expenses
 * @property Collection|Payment[]          $payments
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
        return $this->hasMany(Contact::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'vendor_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'prospect_id');
    }

    public function recurring_invoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class, 'customer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'customer_id');
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
    public function getCustomerEmailAttribute()
    {
        return $this->email;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return RelationFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Subqueries
    |--------------------------------------------------------------------------
    */
}
