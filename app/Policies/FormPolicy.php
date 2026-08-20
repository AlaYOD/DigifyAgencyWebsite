<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use App\Policies\Concerns\ScopesDepartments;

class FormPolicy
{
    use ScopesDepartments;

    public function viewAny(User $user): bool
    {
        return $user->can('forms.view');
    }

    public function view(User $user, Form $form): bool
    {
        return $user->can('forms.view') && $this->inDepartmentScope($user, $form);
    }

    public function create(User $user): bool
    {
        return $user->can('forms.manage');
    }

    public function update(User $user, Form $form): bool
    {
        return $user->can('forms.manage') && $this->inDepartmentScope($user, $form);
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->can('forms.manage') && $this->inDepartmentScope($user, $form);
    }
}
