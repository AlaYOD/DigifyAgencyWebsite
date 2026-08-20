<?php

namespace App\Policies;

use App\Models\FormField;
use App\Models\User;

class FormFieldPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('forms.view');
    }

    public function view(User $user, FormField $field): bool
    {
        return $user->can('view', $field->form);
    }

    public function create(User $user): bool
    {
        return $user->can('forms.manage');
    }

    public function update(User $user, FormField $field): bool
    {
        return $user->can('update', $field->form);
    }

    public function delete(User $user, FormField $field): bool
    {
        return $user->can('delete', $field->form);
    }
}
