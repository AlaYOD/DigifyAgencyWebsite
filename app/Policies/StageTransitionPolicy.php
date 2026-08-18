<?php

namespace App\Policies;

use App\Models\StageTransition;
use App\Models\User;

class StageTransitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view');
    }

    public function view(User $user, StageTransition $transition): bool
    {
        return $user->can('applications.view');
    }

    public function update(User $user, StageTransition $transition): bool
    {
        // Stage transitions are immutable append-only audit records.
        return false;
    }

    public function delete(User $user, StageTransition $transition): bool
    {
        // Stage transitions are immutable append-only audit records.
        return false;
    }
}
