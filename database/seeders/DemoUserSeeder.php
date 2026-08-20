<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $engineering = Department::where('slug->en', 'engineering')->firstOrFail();

        $users = [
            ['name' => 'Demo CEO', 'email' => 'ceo@digify.test', 'role' => 'ceo'],
            ['name' => 'Demo Manager', 'email' => 'manager@digify.test', 'role' => 'manager', 'department_id' => $engineering->id],
            ['name' => 'Demo HR', 'email' => 'hr@digify.test', 'role' => 'hr'],
            ['name' => 'Demo IT', 'email' => 'it@digify.test', 'role' => 'it'],
            ['name' => 'Demo Content Editor', 'email' => 'editor@digify.test', 'role' => 'content_editor'],
        ];

        foreach ($users as $attributes) {
            $role = $attributes['role'];
            unset($attributes['role']);

            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                [...$attributes, 'password' => 'password', 'is_active' => true],
            );

            $user->syncRoles([$role]);
        }

        User::where('email', 'manager@digify.test')
            ->firstOrFail()
            ->managedDepartments()
            ->sync([$engineering->id]);
    }
}
