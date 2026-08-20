<?php

namespace App\Policies;

use App\Models\Locale;
use App\Models\User;

class LocalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, Locale $locale): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, Locale $locale): bool
    {
        return $user->can('settings.manage');
    }

    public function delete(User $user, Locale $locale): bool
    {
        return $user->can('settings.manage') && ! $locale->is_default;
    }
}
