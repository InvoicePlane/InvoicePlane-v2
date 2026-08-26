<?php

namespace Modules\Core\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;
use Modules\Core\Events\UserWasCreated;
use Modules\Core\Events\UserWasUpdated;
use Modules\Core\Models\Company;
use Modules\Core\Models\Upload;
use Modules\Core\Models\User;
use Throwable;

class UserService extends BaseService
{
    public function model(): string
    {
        return User::class;
    }

    public function createUser(array $validatedInput): User
    {
        Arr::forget($validatedInput, 'user_password_confirmation');

        $user = new User(
            $validatedInput
        );

        $user->save();

        event(new UserWasCreated($user));

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->fill($data);

        $user->save();

        event(new UserWasUpdated($user));

        return $user;
    }

    public function deleteUser(User $user): User
    {
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    /**
     * Update the authenticated user's own profile (name, email, language, password).
     * Never touches created_at/updated_at — User::$timestamps is false.
     */
    public function updateProfile(User $user, array $data): User
    {
        if (array_key_exists('password', $data)) {
            if (filled($data['password'])) {
                $data['password'] = Hash::isHashed($data['password'])
                    ? $data['password']
                    : Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        }

        $user->fill($data);
        $user->save();

        event(new UserWasUpdated($user));

        return $user;
    }

    /**
     * Store an avatar file already persisted to disk by the FileUpload component
     * as the user's single avatar Upload record. The previous stored file (if any)
     * is deleted once the replacement Upload record has been saved successfully.
     */
    public function updateAvatar(User $user, string $path, string $disk = 'public'): Upload
    {
        $companyId = $user->getCurrentCompanyId();
        $existing  = $user->avatarUpload()->first();

        $upload = Upload::updateOrCreate(
            [
                'uploadable_type'  => User::class,
                'uploadable_id'    => $user->id,
                'file_description' => 'avatar',
            ],
            [
                'company_id'           => $companyId,
                'user_id'              => $user->id,
                'upload_original_name' => basename($path),
                'upload_stored_name'   => $path,
                'upload_mime_type'     => Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream',
                'upload_url_key'       => Str::random(20),
                'upload_disk'          => $disk,
            ]
        );

        if ($existing && $existing->upload_stored_name !== $path) {
            Storage::disk($existing->upload_disk)->delete($existing->upload_stored_name);
        }

        return $upload;
    }

    /**
     * Remove the user's avatar Upload record and its stored file, if one exists.
     */
    public function removeAvatar(User $user): bool
    {
        $existing = $user->avatarUpload()->first();

        if ( ! $existing) {
            return false;
        }

        Storage::disk($existing->upload_disk)->delete($existing->upload_stored_name);
        $existing->delete();

        return true;
    }

    /**
     * Guard against switching a user's active tenant to a company they aren't a
     * member of. Called from the record resolved by Filament's table-action
     * dispatch, which is not something callers otherwise verify — see #687.
     *
     * @throws AuthorizationException
     */
    public function assertBelongsToCompany(User $user, Company|int $company): void
    {
        $companyId  = $company instanceof Company ? $company->id : $company;
        $isElevated = $user->hasRole(UserRole::elevated());

        if ( ! $isElevated && ! $user->companies()->whereKey($companyId)->exists()) {
            throw new AuthorizationException(
                trans('ip.user_not_in_company') ?? 'You do not have access to this company.'
            );
        }
    }
}
