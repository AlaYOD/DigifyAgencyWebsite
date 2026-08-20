<?php

namespace App\Policies;

use App\Models\Revision;
use App\Models\User;

class RevisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('activity.view');
    }

    public function view(User $user, Revision $revision): bool
    {
        return $user->can('activity.view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['ceo', 'manager', 'content_editor']);
    }

    public function delete(User $user, Revision $revision): bool
    {
        return $user->hasRole('ceo');
    }
}
