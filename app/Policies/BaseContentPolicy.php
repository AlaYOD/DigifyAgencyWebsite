<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ScopesDepartments;
use Illuminate\Database\Eloquent\Model;

abstract class BaseContentPolicy
{
    use ScopesDepartments;

    abstract protected function permissionPrefix(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.view');
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can($this->permissionPrefix().'.view') && $this->inDepartmentScope($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.create');
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can($this->permissionPrefix().'.update') && $this->inDepartmentScope($user, $record);
    }

    public function publish(User $user, Model $record): bool
    {
        return $user->can($this->permissionPrefix().'.publish') && $this->inDepartmentScope($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can($this->permissionPrefix().'.delete') && $this->inDepartmentScope($user, $record);
    }
}
