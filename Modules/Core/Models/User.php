<?php

namespace Modules\Core\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Database\Factories\UserFactory;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

/**
 * @property int                           $id
 * @property string                        $name
 * @property string                        $email
 * @property mixed                         $email_verified_at
 * @property string                        $password
 * @property string                        $remember_token
 * @property mixed                         $created_at
 * @property mixed                         $updated_at
 * @property Invoice[]                     $invoices
 * @property Note[]                        $notes
 * @property Collection|Attachment[]       $attachments
 * @property Collection|Expense[]          $expenses
 * @property Collection|Invoice[]          $invoices
 * @property Collection|Note[]             $notes
 * @property Collection|Quote[]            $quotes
 * @property Collection|RecurringInvoice[] $recurringInvoices
 * @property Upload[]                      $uploads
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasFactory;
    use Notifiable;

    public $timestamps = false;

    protected $hidden = [
        'password',
        'user_password_confirmation',
        'remember_token',
        'user_psalt',
        'user_passwordreset_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
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

        static::created(function ($user): void {
            event(new UserCreated($user));
        });

        static::deleted(function ($user): void {
            event(new UserDeleted($user));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function attachments(): HasMany
    {
        // return $this->hasMany(Attachment::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'company_user',
            'user_id',
            'company_id',
        );
    }

    public function getCurrentCompanyId(): ?int
    {
        return session('current_company_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getUserTypeAttribute(): string
    {
        return ($this->customer_id) ? 'customer' : 'admin';
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setPasswordAttribute($password): void
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUserType($query, $userType)
    {
        if ($userType == 'customer') {
            $query->where('customer_id', '<>', 0);
        } elseif ($userType == 'admin') {
            $query->where('customer_id', 0);
        }

        return $query;
    }

    // ——————————————————————————————————————————————————————————————
    // |                             FILAMENT PANEL INTEGRATION                           |
    // ——————————————————————————————————————————————————————————————

    public function getFilamentName(): string
    {
        return $this->getAttributeValue('name');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    public function canAccessPanel($panel): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
