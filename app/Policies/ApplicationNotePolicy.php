<?php

namespace App\Policies;

use App\Models\ApplicationNote;
use App\Models\User;

class ApplicationNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.note');
    }

    public function view(User $user, ApplicationNote $note): bool
    {
        return $user->can('applications.note');
    }

    public function create(User $user): bool
    {
        return $user->can('applications.note');
    }

    public function update(User $user, ApplicationNote $note): bool
    {
        return $user->can('applications.note');
    }

    public function delete(User $user, ApplicationNote $note): bool
    {
        return $user->can('applications.note');
    }
}
