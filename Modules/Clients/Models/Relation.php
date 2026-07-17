<?php

namespace Modules\Clients\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Database\Factories\RelationFactory;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

/**
 * @property int                  $id
 * @property int                  $company_id
 * @property int|null             $primary_contact_id
 * @property RelationType         $relation_type
 * @property RelationStatus       $relation_status
 * @property string               $relation_number
 * @property string               $company_name
 * @property string|null          $email
 * @property string|null          $trading_name
 * @property string|null          $unique_name
 * @property string|null          $id_number
 * @property string|null          $coc_number
 * @property string|null          $vat_number
 * @property CarbonInterface      $registered_at
 * @property CarbonInterface|null $deleted_at
 * @property mixed                $created_at
 * @property mixed                $updated_at
 * @property Invoice[]            $invoices
 * @property Quote[]              $quotes
 * @property Project[]            $projects
 * @property Contact              $contact
 * @property string|null          $currency_code
 * @property string|null          $language
 * @property array                $email_cc
 * @property Company              $company
 * @property Collection|Contact[] $contacts
 * @property Collection|Expense[] $expenses
 * @property Collection|Payment[] $payments
 * @property Collection|User[]    $users
 * @property Task[]               $tasks
 */
class Relation extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'relations';

    protected $casts = [
        'relation_type'      => RelationType::class,
        'relation_status'    => RelationStatus::class,
        'enable_e_invoicing' => 'boolean',
    ];

    protected $guarded = [];

    protected $appends = ['email_cc'];

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

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function primaryAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('is_primary', true);
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'billing');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'shipping');
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicationable');
    }

    public function ccEmailCommunications(): MorphMany
    {
        return $this->communications()->where('communication_type', CommunicationType::INVOICE_CC->value);
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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'customer_id');
    }

    /*
     * Deleting a relation with linked records would orphan financial
     * history, so delete actions and the service guard both check this.
     */
    public function hasLinkedRecords(): bool
    {
        return $this->invoices()->withoutGlobalScopes()->exists()
            || $this->quotes()->withoutGlobalScopes()->exists()
            || $this->payments()->withoutGlobalScopes()->exists()
            || $this->expenses()->withoutGlobalScopes()->exists()
            || $this->tasks()->withoutGlobalScopes()->exists()
            || $this->projects()->withoutGlobalScopes()->exists();
    }

    /**
     * Define a one-to-many relationship to User models.
     *
     * @return HasMany the has-many relationship for User models
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getCustomerEmailAttribute(): ?string
    {
        return $this->email;
    }

    public function getEmailCcAttribute(): array
    {
        return $this->ccEmailCommunications()
            ->pluck('communication_value')
            ->all();
    }

    /*public function getPrimaryContactAttribute(): string
    {
        return mb_trim($this->primary_ontact?->first_name . ' ' . $this->primary_contact?->last_name);
    }*/
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
