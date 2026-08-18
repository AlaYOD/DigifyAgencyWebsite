<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'pages.view', 'pages.create', 'pages.update', 'pages.publish', 'pages.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.publish', 'posts.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.publish',
            'menus.view', 'menus.manage', 'media.upload',
            'jobs.view', 'jobs.create', 'jobs.update', 'jobs.publish', 'jobs.close',
            'applications.view', 'applications.viewPii', 'applications.move',
            'applications.note', 'applications.export', 'applications.delete',
            'forms.view', 'forms.manage', 'submissions.view', 'submissions.export',
            'users.manage', 'settings.manage', 'redirects.manage', 'activity.view', 'reports.view',
        ];

        $permissionModels = collect($permissions)
            ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'));

        $roles = [
            'ceo' => [
                'pages.view', 'pages.publish', 'pages.delete',
                'posts.view', 'posts.publish', 'projects.view', 'projects.publish',
                'media.upload', 'jobs.view', 'jobs.publish', 'jobs.close',
                'applications.view', 'applications.viewPii', 'applications.note', 'applications.export',
                'submissions.view', 'submissions.export', 'activity.view', 'reports.view',
            ],
            'manager' => [
                'pages.view', 'pages.create', 'pages.update', 'pages.publish',
                'posts.view', 'posts.create', 'posts.update', 'posts.publish',
                'projects.view', 'projects.create', 'projects.update', 'media.upload',
                'jobs.view', 'jobs.create', 'jobs.update', 'jobs.close',
                'applications.view', 'applications.viewPii', 'applications.move', 'applications.note',
                'submissions.view', 'reports.view',
            ],
            'hr' => [
                'pages.view', 'posts.view', 'media.upload',
                'jobs.view', 'jobs.create', 'jobs.update', 'jobs.publish', 'jobs.close',
                'applications.view', 'applications.viewPii', 'applications.move', 'applications.note',
                'applications.export', 'applications.delete', 'forms.view', 'forms.manage',
                'submissions.view', 'submissions.export', 'reports.view',
            ],
            'it' => [
                'pages.view', 'posts.view', 'projects.view',
                'menus.view', 'menus.manage', 'media.upload', 'jobs.view', 'applications.view',
                'forms.view', 'forms.manage', 'submissions.view', 'users.manage', 'settings.manage',
                'redirects.manage', 'activity.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions(
                $permissionModels->whereIn('name', $rolePermissions),
            );
        }
    }
}
