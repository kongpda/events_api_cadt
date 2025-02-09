<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organizer;
use App\Models\User;

final class OrganizerPolicy
{
    public function update(User $user, Organizer $organizer): bool
    {
        return $user->id === $organizer->user_id;
    }

    public function delete(User $user, Organizer $organizer): bool
    {
        return $user->id === $organizer->user_id;
    }
}
