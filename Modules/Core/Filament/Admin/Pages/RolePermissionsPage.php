<?php

namespace Modules\Core\Filament\Admin\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Enums\Permission as PermissionEnum;
use Modules\Core\Enums\UserRole;
use Modules\Core\Services\RolesService;
use Spatie\Permission\Models\Role;

class RolePermissionsPage extends Page
{
    public array $matrix = [];

    public Collection $roles;

    public array $groupedPerms = [];

    public string $superAdmin = '';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'core::filament.admin.pages.role-permissions-page';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if ( ! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->can(PermissionEnum::MANAGE_ROLES->value);
    }

    public static function getGroupedPermissions(): array
    {
        $domainGroups = [
            trans('ip.clients')    => ['relations', 'contacts'],
            trans('ip.financials') => ['invoices', 'quotes', 'payments'],
            trans('ip.operations') => ['products', 'projects', 'expenses', 'tasks'],
            trans('ip.system')     => ['users', 'roles', 'companies', 'settings', 'permissions',
                'reports', 'email-templates', 'tax-rates', 'dashboard'],
        ];

        $grouped   = array_fill_keys(array_keys($domainGroups), []);
        $ungrouped = [];

        foreach (PermissionEnum::cases() as $perm) {
            $placed = false;
            foreach ($domainGroups as $group => $domains) {
                foreach ($domains as $domain) {
                    if (str_contains($perm->value, $domain)) {
                        $grouped[$group][] = $perm;
                        $placed            = true;
                        break 2;
                    }
                }
            }
            if ( ! $placed) {
                $ungrouped[] = $perm;
            }
        }

        if ($ungrouped) {
            $grouped[trans('ip.other')] = $ungrouped;
        }

        return array_filter($grouped);
    }

    public function mount(): void
    {
        $this->roles        = Role::all();
        $this->groupedPerms = static::getGroupedPermissions();
        $this->superAdmin   = UserRole::SUPER_ADMIN->value;

        $roles = $this->roles->keyBy('name');
        foreach ($roles as $roleName => $role) {
            $isSuperAdmin = $roleName === UserRole::SUPER_ADMIN->value;
            $granted      = $isSuperAdmin ? [] : $role->permissions->pluck('name')->flip();
            foreach (PermissionEnum::cases() as $perm) {
                $this->matrix[$roleName][$perm->value] = $isSuperAdmin || isset($granted[$perm->value]);
            }
        }
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        app(RolesService::class)->syncPermissionsFromMatrix($this->matrix, auth()->user());

        Notification::make()->title(trans('ip.role_permissions_updated'))->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->action('save')
                ->label(trans('ip.save_changes'))
                ->color('primary'),
        ];
    }
}
