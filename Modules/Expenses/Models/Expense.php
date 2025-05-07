<?php

namespace Modules\Expenses\Models;

use Modules\Expenses\Enums\ExpenseType;

use Modules\Clients\Enums\RelationType;

use Modules\Core\Support\DateFormatter;

use Modules\Expenses\Models\ExpenseCategory;

use Modules\Invoices\Models\Invoice;

use Modules\Expenses\Models\Expense;

use Modules\Expenses\Database\Factories\ExpenseFactory;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Expenses\Models\ExpenseItem;

use Modules\Expenses\Enums\ExpenseStatus;

use Modules\Core\Models\Company;

use Modules\Core\Support\NumberFormatter;

use Modules\Clients\Models\Relation;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Database\Factories\ExpenseFactory;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;

/**
 * @property int             $id
 * @property int             $company_id
 * @property int|null        $invoice_id
 * @property int             $customer_id
 * @property int             $vendor_id
 * @property int             $category_id
 * @property int             $user_id
 * @property Carbon          $expensed_at
 * @property string          $amount
 * @property string|null     $description
 * @property ExpenseCategory $expense_category
 * @property Company         $company
 * @property Customer        $customer
 * @property Invoice|null    $invoice
 * @property User            $user
 * @property ExpenseVendor   $expense_vendor
 */
class Expense extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'expense_status' => ExpenseStatus::class,
        'expense_type'   => ExpenseType::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Observer
    |--------------------------------------------------------------------------
    */
    public static function boot(): void
    {
        parent::boot();

        static::created(function ($expense): void {
            event(new ExpenseCreated($expense));
        });

        static::saved(function ($expense): void {
            event(new CheckAttachment($expense));
        });

        static::saving(function ($expense): void {
            event(new ExpenseSaving($expense));
        });

        static::deleting(function ($expense): void {
            event(new ExpenseDeleting($expense));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ENUM! ENUM! ENUM!
    |--------------------------------------------------------------------------
    */
    public static function getTimeFrames(): array
    {
        return [
            0 => trans('ip.all_time'),
            1 => trans('ip.month_to_date'),
            2 => trans('ip.year_to_date'),
            3 => trans('ip.last_month'),
            4 => trans('ip.last_year'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function attachments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        // return $this->morphMany(Attachment::class, 'attachable');
    }

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'relation_id')
            ->where('relation_type', RelationType::CUSTOMER->value);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function expenseItems(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'relation_id')
            ->where('relation_type', RelationType::VENDOR->value);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getAttachmentPathAttribute(): string
    {
        return attachment_path('expenses/' . $this->id);
    }

    public function getAttachmentPermissionOptionsAttribute(): array
    {
        return [
            '0' => trans('ip.not_visible'),
            '1' => trans('ip.visible'),
        ];
    }

    public function getFormattedAmountAttribute(): string
    {
        return CurrencyFormatter::format($this->amount);
    }

    public function getFormattedTaxAttribute(): string
    {
        return CurrencyFormatter::format($this->tax);
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return nl2br($this->description);
    }

    public function getFormattedExpenseDateAttribute(): string
    {
        return DateFormatter::format($this->expensed_at);
    }

    public function getFormattedNumericAmountAttribute(): float
    {
        return NumberFormatter::format($this->amount);
    }

    public function getFormattedNumericTaxAttribute(): float
    {
        return NumberFormatter::format($this->tax);
    }

    public function getHasBeenBilledAttribute(): bool
    {
        return (bool) ($this->invoice_id);
    }

    public function getIsBillableAttribute(): bool
    {
        return (bool) ($this->customer_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCategoryId($query, $categoryId = null)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    public function scopeCompanyProfileId($query, $companyId = null)
    {
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    public function scopeDefaultQuery($query)
    {
        return $query->select(
            'expenses.*',
            'expense_categories.name AS category_name',
            'expense_vendors.name AS vendor_name',
            'customers.unique_name AS client_name'
        )
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
            ->leftJoin('expense_vendors', 'expense_vendors.id', '=', 'expenses.vendor_id')
            ->leftJoin('customers', 'customers.id', '=', 'expenses.customer_id');
    }

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where('expenses.expensed_at', 'like', '%' . $keywords . '%')
                ->orWhere('expenses.description', 'like', '%' . $keywords . '%')
                ->orWhere('expense_vendors.name', 'like', '%' . $keywords . '%')
                ->orWhere('customers.name', 'like', '%' . $keywords . '%')
                ->orWhere('expense_categories.name', 'like', '%' . $keywords . '%');
        }

        return $query;
    }

    public function scopeStatus($query, $status = null)
    {
        if ($status) {
            switch ($status) {
                case 'billed':
                    $query->where('invoice_id', '<>', 0);
                    break;
                case 'not_billed':
                    $query->where('customer_id', '<>', 0)->where('invoice_id', '=', 0);
                    break;
                case 'not_billable':
                    $query->where('customer_id', 0);
                    break;
            }
        }

        return $query;
    }

    public function scopeTimeFrame($query, $timeFrame = null)
    {
        if ($timeFrame) {
            switch ($timeFrame) {
                /*case 0:
                    $query->where('invoice_id', '<>', 0);
                    break;
                */
                //Month to Date
                case 1:
                    $startOfMonth = Carbon::now()->firstOfMonth();
                    $today        = Carbon::now()->today();
                    $query->where(function ($query) use ($startOfMonth, $today): void {
                        $query->whereBetween('expensed_at', [$startOfMonth, $today]);
                    });
                    break;
                    //Year to Date
                case 2:
                    $startOfYear = Carbon::now()->startOfYear();
                    $today       = Carbon::now()->today();
                    $query->where(function ($query) use ($startOfYear, $today): void {
                        $query->whereBetween('expensed_at', [$startOfYear, $today]);
                    });
                    break;
                    //Last Month
                case 3:
                    $startOfLastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
                    $endOfLastMonth   = Carbon::now()->subMonthNoOverflow()->endOfMonth();
                    $query->where(function ($query) use ($startOfLastMonth, $endOfLastMonth): void {
                        $query->whereBetween('expensed_at', [$startOfLastMonth, $endOfLastMonth]);
                    });
                    break;
                    //Last Year
                case 4:
                    $startOfLastYear = Carbon::now()->subYear()->startOfYear();
                    $endOfLastYear   = Carbon::now()->subYear()->endOfYear();
                    $query->where(function ($query) use ($startOfLastYear, $endOfLastYear): void {
                        $query->whereBetween('expensed_at', [$startOfLastYear, $endOfLastYear]);
                    });
                    break;
            }
        }

        return $query;
    }

    public function scopeVendorId($query, $vendorId = null)
    {
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
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
        return ExpenseFactory::new();
    }
}
