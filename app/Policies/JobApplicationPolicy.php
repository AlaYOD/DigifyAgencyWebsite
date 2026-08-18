<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view');
    }

    public function view(User $user, JobApplication $application): bool
    {
        return $user->can('applications.view') && $this->inScope($user, $application);
    }

    public function viewPii(User $user, JobApplication $application): bool
    {
        return $user->can('applications.viewPii') && $this->inScope($user, $application);
    }

    public function create(User $user): bool
    {
        return $user->can('applications.view');
    }

    public function move(User $user, JobApplication $application): bool
    {
        return $user->can('applications.move') && $this->inScope($user, $application);
    }

    public function note(User $user, JobApplication $application): bool
    {
        return $user->can('applications.note') && $this->inScope($user, $application);
    }

    public function export(User $user, JobApplication $application): bool
    {
        return $user->can('applications.export') && $user->hasAnyRole(['ceo', 'hr']);
    }

    public function delete(User $user, JobApplication $application): bool
    {
        return $user->hasRole('hr') && $user->can('applications.delete');
    }

    private function inScope(User $user, JobApplication $application): bool
    {
        if ($user->hasAnyRole(['ceo', 'hr'])) {
            return true;
        }

        if ($user->hasRole('it')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->managedDepartments()
                ->whereKey($application->jobPosting->department_id)
                ->exists();
        }

        return false;
    }
}
