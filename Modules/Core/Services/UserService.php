<?php

namespace Modules\Core\Services;

use Modules\Core\Events\UserWasCreated;

use Modules\Core\Models\User;

use Modules\Core\Services\BaseService;

use Modules\Core\Events\UserWasUpdated;

use Modules\Core\Services\UserService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserService extends BaseService
{
    public function model(): string
    {
        return User::class;
    }

    public function create(array $validatedInput): User
    {
        Arr::forget($validatedInput, 'user_password_confirmation');

        $user = new User(
            $validatedInput
        );

        $user->save();

        event(new UserWasCreated($user));

        return $user;
    }

    public function update(array $validatedInput, $userToUpdate): Model
    {
        $userToUpdate->fill($validatedInput);

        $userToUpdate->save();

        event(new UserWasUpdated($userToUpdate));

        return $userToUpdate;
    }
}
