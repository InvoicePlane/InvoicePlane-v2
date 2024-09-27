<?php

namespace Modules\Core\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Core\Database\Factories\UserFactory;
use Modules\Core\Enums\UserType;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public const CREATED_AT = 'user_date_created';

    public const UPDATED_AT = 'user_date_modified';

    /**
     * The user admin role with read and write
     * privileges.
     */
    public const ADMIN = 1;

    /**
     * The user with guest read only privilege
     * known in IPv1.5 as guest_read_only.
     */
    public const CLIENT = 2;

    public $table = 'users';

    public $timestamps = true;

    public $orderable = [
        'user_name',
        'user_email',
    ];

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_type',
        'user_active',
        'user_date_created',
        'user_date_modified',
        'user_language',
        'user_name',
        'user_company',
        'user_address_1',
        'user_address_2',
        'user_city',
        'user_state',
        'user_zip',
        'user_country',
        'user_phone',
        'user_fax',
        'user_mobile',
        'user_email',
        'user_password',
        'user_password_confirmation',
        'user_web',
        'user_vat_id',
        'user_tax_code',
        'user_psalt',
        'user_all_clients',
        'user_passwordreset_token',
        'user_subscribernumber',
        'user_iban',
        'user_gln',
        'user_rcc',
    ];

    protected $dates = [
        'user_date_created',
        'user_date_modified',
        'deleted_at',
    ];

    protected $hidden = [
        'user_password',
        'user_password_confirmation',
        'remember_token',
        'user_psalt',
        'user_passwordreset_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    private ?string $user_email = null;

    private ?string $user_password = null;

    public function getFilamentName(): string
    {
        return $this->getAttributeValue('user_name');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->user_type === UserType::ADMIN->value;
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
