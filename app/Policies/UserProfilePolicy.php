<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

final class UserProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserProfile $profile): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function delete(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
