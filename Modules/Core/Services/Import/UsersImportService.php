<?php

namespace Modules\Core\Services\Import;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\User;

class UsersImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_users'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['users']);

        $this->importUsers();

        return $this->stats;
    }

    private function importUsers(): void
    {
        $users = $this->getImportData('ip_users');

        foreach ($users as $v1User) {
            // Skip users without valid email
            if (empty($v1User->user_email) || ! filter_var($v1User->user_email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Check if user already exists by email
            $existingUser = User::where('email', $v1User->user_email)->first();

            if ($existingUser) {
                // Attach existing user to company if not already attached
                if ( ! $existingUser->companies()->where('companies.id', $this->companyId)->exists()) {
                    $existingUser->companies()->attach($this->companyId);
                }
                $this->idMappings['users'][$v1User->user_id] = $existingUser->id;
                continue;
            }

            $user = User::create([
                'name'  => $v1User->user_name ?? 'Imported User',
                'email' => $v1User->user_email,
                // For security, do not reuse legacy v1 password hashes.
                // Always assign a new random password and require a password reset in v2.
                'password' => Hash::make(str()->random(32)),
            ]);

            // Attach new user to the target company
            $user->companies()->attach($this->companyId);

            $this->idMappings['users'][$v1User->user_id] = $user->id;
            $this->stats['users']++;
        }
    }
}
