<?php

namespace Modules\Core\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\UserFactory;
use Modules\Core\Enums\UserRole;
use Modules\Core\Support\ProfileImage\ProfileImageFactory;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int                     $id
 * @property string                  $name
 * @property string                  $email
 * @property mixed                   $email_verified_at
 * @property string                  $language
 * @property string                  $password
 * @property string                  $remember_token
 * @property mixed                   $created_at
 * @property mixed                   $updated_at
 * @property Invoice[]               $invoices
 * @property Note[]                  $notes
 * @property Collection|Attachment[] $attachments
 * @property Collection|Expense[]    $expenses
 * @property Collection|Quote[]      $quotes*           @property Upload[]                $uploads
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, HasTenants, HasDefaultTenant, MustVerifyEmailContract
{
    use CanResetPassword;
    use HasFactory;
    use HasRoles;
    use MustVerifyEmail;
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
        'is_active'         => 'boolean',
        'last_login'        => 'datetime',
        'preferences'       => 'array',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Observer
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function attachments(): ?HasMany
    {
        // return $this->hasMany(Attachment::class);
        return null;
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'company_user',
            'user_id',
            'company_id',
        )
            ->using(CompanyUser::class);
    }

    public function getCurrentCompanyId(): ?int
    {
        $companyId = session('current_company_id');

        if ( ! $companyId) {
            $companyId = $this->companies()->first()?->id;
        }

        return $companyId;
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

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function avatarUpload(): MorphOne
    {
        return $this->morphOne(Upload::class, 'uploadable')
            ->where('file_description', 'avatar')
            ->latestOfMany();
    }

    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // ——————————————————————————————————————————————————————————————
    // |                             FILAMENT PANEL INTEGRATION                           |
    // ——————————————————————————————————————————————————————————————
    public function getFilamentName(): string
    {
        return $this->getAttributeValue('name');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($avatar = $this->avatarUpload) {
            return Storage::disk($avatar->upload_disk)->url($avatar->upload_stored_name);
        }

        return ProfileImageFactory::create()->getProfileImageUrl($this);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN->value);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // SuperAdmin, Admin, Assistance can access any panel
        if (
            $this->hasRole(UserRole::SUPER_ADMIN->value)
            || $this->hasRole(UserRole::ADMIN->value)
            || $this->hasRole(UserRole::ASSIST->value)
        ) {
            return true;
        }

        // UserAdmin and User can only access the 'company' panel
        if ($panel->getId() === 'company') {
            return $this->hasRole(UserRole::CUSTOMER_ADMIN->value)
                || $this->hasRole(UserRole::CUSTOMER->value);
        }

        // All other roles or panels not explicitly allowed
        return false;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->companies()->whereKey($tenant->getKey())->exists();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->companies;
    }

    /**
     * Filament tenancy: return the user's default tenant (first company).
     */
    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->companies()->first();
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
