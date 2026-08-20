<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ScopesDepartments
{
    protected function inDepartmentScope(User $user, Model $record): bool
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it', 'content_editor'])) {
            return true;
        }

        return $user->hasRole('manager')
            && filled($record->getAttribute('department_id'))
            && $user->managedDepartments()->whereKey($record->getAttribute('department_id'))->exists();
    }
}
