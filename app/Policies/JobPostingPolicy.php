<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('jobs.view');
    }

    public function view(User $user, JobPosting $posting): bool
    {
        return $user->can('jobs.view') && $this->inScope($user, $posting);
    }

    public function create(User $user, ?JobPosting $posting = null): bool
    {
        return $user->can('jobs.create') && ($posting === null || $this->inScope($user, $posting));
    }

    public function update(User $user, JobPosting $posting): bool
    {
        return $user->can('jobs.update') && $this->inScope($user, $posting);
    }

    public function publish(User $user, JobPosting $posting): bool
    {
        return $user->can('jobs.publish');
    }

    public function close(User $user, JobPosting $posting): bool
    {
        return $user->can('jobs.close') && $this->inScope($user, $posting);
    }

    public function delete(User $user, JobPosting $posting): bool
    {
        return $user->can('jobs.delete');
    }

    private function inScope(User $user, JobPosting $posting): bool
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->managedDepartments()->whereKey($posting->department_id)->exists();
        }

        return false;
    }
}
