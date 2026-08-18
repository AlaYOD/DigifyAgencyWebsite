<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['slug' => 'engineering', 'en' => 'Engineering', 'ar' => 'الهندسة'],
            ['slug' => 'design', 'en' => 'Design', 'ar' => 'التصميم'],
            ['slug' => 'marketing', 'en' => 'Marketing', 'ar' => 'التسويق'],
            ['slug' => 'operations', 'en' => 'Operations', 'ar' => 'العمليات'],
        ];

        foreach ($departments as $sortOrder => $department) {
            Department::updateOrCreate(
                ['slug->en' => $department['slug']],
                [
                    'slug' => ['en' => $department['slug'], 'ar' => $department['slug']],
                    'name' => ['en' => $department['en'], 'ar' => $department['ar']],
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
