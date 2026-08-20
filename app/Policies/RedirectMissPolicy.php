<?php

namespace App\Policies;

use App\Models\RedirectMiss;
use App\Models\User;

class RedirectMissPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('redirects.manage');
    }

    public function view(User $user, RedirectMiss $miss): bool
    {
        return $user->can('redirects.manage');
    }

    public function delete(User $user, RedirectMiss $miss): bool
    {
        return $user->can('redirects.manage');
    }
}
