<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\AbstractDocumentModel;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Database\Factories\ExpenseFactory;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Invoices\Models\Invoice;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property int|null                 $invoice_id
 * @property int|null                 $customer_id
 * @property int|null                 $vendor_id
 * @property int|null                 $category_id
 * @property int|null                 $user_id
 * @property string                   $expense_number
 * @property ExpenseStatus            $expense_status
 * @property ExpenseType              $expense_type
 * @property Carbon                   $expensed_at
 * @property float                    $expense_amount
 * @property string|null              $description
 * @property Company                  $company
 * @property ExpenseCategory|null     $expense_category
 * @property Relation|null            $relation
 * @property Invoice|null             $invoice
 * @property User|null                $user
 * @property Collection|ExpenseItem[] $expense_items
 */
class Expense extends AbstractDocumentModel
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'expensed_at'    => 'datetime',
        'expense_amount' => 'float',
        'expense_status' => ExpenseStatus::class,
        'expense_type'   => ExpenseType::class,
    ];

    protected $guarded = [];

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
    public function attachments(): ?MorphMany
    {
        // return $this->morphMany(Attachment::class, 'attachable');
        return null;
    }

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'customer_id');
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
            ->belongsTo(Relation::class, 'vendor_id');
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
        return ExpenseFactory::new();
    }
}
