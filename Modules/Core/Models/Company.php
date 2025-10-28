<?php

namespace Modules\Core\Models;

use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\CompanyFactory;
use Modules\Core\Enums\UserRole;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

/*
 * @property int                           $id
 * @property string                        $search_code
 * @property string                        $name
 * @property string                        $slug
 * @property string|null                   $vat_number
 * @property string|null                   $id_number
 * @property string|null                   $coc_number
 * @property string|null                   $logo
 * @property string                        $quote_template
 * @property string                        $invoice_template
 * @property Collection|Addressable[]      $addressables
 * @property Collection|Address[]          $addresses
 * @property Collection|Communication[]    $communications
 * @property Collection|User[]             $companyUsers
 * @property Collection|Contact[]          $contacts
 * @property Collection|CustomFieldValue[] $custom_field_values
 * @property Collection|CustomField[]      $custom_fields
 * @property Collection|DocumentGroup[]    $document_groups
 * @property Collection|EmailTemplate[]    $email_templates
 * @property Collection|ExpenseCategory[]  $expense_categories
 * @property Collection|ExpenseItem[]      $expense_items
 * @property Collection|Expense[]          $expenses
 * @property Collection|InvoiceItem[]      $invoice_items
 * @property Collection|Invoice[]          $invoices
 * @property Collection|Note[]             $notes
 * @property Collection|Payment[]          $payments
 * @property Collection|ProductCategory[]  $product_categories
 * @property Collection|ProductUnit[]      $product_units
 * @property Collection|Product[]          $products
 * @property Collection|Project[]          $projects
 * @property Collection|QuoteItem[]        $quote_items
 * @property Collection|Quote[]            $quotes
 * @property Collection|RecurringInvoice[] $recurring_invoices
 * @property Collection|Relation[]         $relations
 * @property Collection|Task[]             $tasks
 * @property Collection|TaxRate[]          $tax_rates
 * @property Collection|UploadDetail[]     $upload_details
 * @property Collection|Upload[]           $uploads
 */
class Company extends Model implements HasName, HasCurrentTenantLabel
{
    use HasFactory;

    public $timestamps = false;

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
    public function customerAdmins(): BelongsToMany
    {
        return $this->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', UserRole::CUSTOMER_ADMIN->value); // 'client_admin'
            });
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the company's primary address.
     */
    public function primaryAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('is_primary', true);
    }

    /**
     * Get the company's billing address.
     */
    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'billing');
    }

    /**
     * Get the company's shipping address.
     */
    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', 'shipping');
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicable');
    }

    public function companyUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('is_owner');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function custom_field_values(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'customizable');
    }

    public function custom_fields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }

    public function documentGroups(): HasMany
    {
        return $this->hasMany(DocumentGroup::class);
    }

    public function email_templates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function expense_categories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expense_items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoice_items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function product_categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function product_units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quote_items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function upload_details(): HasMany
    {
        return $this->hasMany(UploadDetail::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /**
     * Get all users associated with this company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'company_user',
            'company_id',
            'user_id'
        )
            ->using(CompanyUser::class);
    }

    // ——————————————————————————————————————————————————————————————
    // |                             FILAMENT PANEL INTEGRATION                           |
    // ——————————————————————————————————————————————————————————————
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getCurrentTenantLabel(): string
    {
        return $this->name;
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

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }
}
