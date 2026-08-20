<?php

namespace App\Filament\Support;

use App\Models\Department;

class AdminOptions
{
    public static function departments(): array
    {
        $query = Department::query()->where('is_active', true)->orderBy('sort_order');
        $user = auth()->user();

        if ($user?->hasRole('manager')) {
            $query->whereIn('id', $user->managedDepartments()->select('departments.id'));
        }

        return $query->get()->mapWithKeys(fn (Department $department): array => [
            $department->id => $department->getTranslation('name', app()->getLocale(), false)
                ?: $department->getTranslation('name', 'en'),
        ])->all();
    }

    public static function enum(string $enum): array
    {
        return collect($enum::cases())->mapWithKeys(fn ($case): array => [
            $case->value => str($case->value)->replace('_', ' ')->title()->toString(),
        ])->all();
    }
}
