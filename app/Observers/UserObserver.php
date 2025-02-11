<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;

final class UserObserver
{
    public function created(User $user): void
    {
        UserProfile::create([
            'user_id' => $user->id,
            'first_name' => explode(' ', $user->name)[0] ?? '',
            'last_name' => explode(' ', $user->name)[1] ?? '',
            'status' => UserStatus::ACTIVE->value,
        ]);
    }
}
