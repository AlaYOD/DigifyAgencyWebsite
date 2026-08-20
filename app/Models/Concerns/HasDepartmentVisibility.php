<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasDepartmentVisibility
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it', 'content_editor'])) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->whereIn('department_id', $user->managedDepartments()->select('departments.id'));
        }

        return $query->whereRaw('1 = 0');
    }
}
