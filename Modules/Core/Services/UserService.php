<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Events\UserWasCreated;
use Modules\Core\Events\UserWasUpdated;
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
}
