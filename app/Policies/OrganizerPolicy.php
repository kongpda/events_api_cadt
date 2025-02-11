<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class OrganizerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Organizers are public
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Organizer $organizer): bool
    {
        return true; // Organizers are public
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create organizers
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organizer $organizer): bool
    {
        return $user->id === $organizer->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organizer $organizer): bool
    {
        return $user->id === $organizer->user_id;
    }
}
