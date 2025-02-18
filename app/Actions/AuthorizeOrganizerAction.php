<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

final class AuthorizeOrganizerAction
{
    /**
     * Determine if the user is authorized to perform actions on the model.
     */
    public function execute(User $user, Model $model): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return $user->organizer?->id === $model->organizer_id;
    }
}
