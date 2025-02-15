<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\UserProfileService;

final class UserObserver
{
    public function __construct(
        private readonly UserProfileService $profileService
    ) {}

    public function created(User $user): void
    {
        $this->profileService->createInitialProfile($user);
    }
}
