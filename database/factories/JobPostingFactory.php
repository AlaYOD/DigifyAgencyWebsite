<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        $departmentId = Department::query()->value('id');

        return [
            'department_id' => $departmentId,
            'title' => ['en' => fake()->jobTitle(), 'ar' => fake()->randomElement([
                "\u{0645}\u{0647}\u{0646}\u{062F}\u{0633} \u{0628}\u{0631}\u{0645}\u{062C}\u{064A}\u{0627}\u{062A}",
                "\u{0645}\u{0635}\u{0645}\u{0645} \u{0645}\u{0646}\u{062A}\u{062C}\u{0627}\u{062A}",
                "\u{0645}\u{062F}\u{064A}\u{0631} \u{0645}\u{0634}\u{0627}\u{0631}\u{064A}\u{0639}",
            ])],
            'slug' => ['en' => fake()->unique()->slug(), 'ar' => fake()->unique()->slug()],
            'summary' => ['en' => fake()->sentence(), 'ar' => 'ملخص الوظيفة'],
            'description' => ['en' => fake()->paragraph(), 'ar' => 'وصف الوظيفة'],
            'responsibilities' => ['en' => fake()->paragraph(), 'ar' => 'المسؤوليات'],
            'requirements' => ['en' => fake()->paragraph(), 'ar' => 'المتطلبات'],
            'benefits' => ['en' => fake()->paragraph(), 'ar' => 'المزايا'],
            'employment_type' => 'full_time',
            'workplace_type' => 'hybrid',
            'experience_level' => 'mid',
            'salary_is_public' => false,
            'positions_count' => 1,
            'status' => 'draft',
            'is_featured' => false,
            'views_count' => 0,
            'applications_count' => 0,
        ];
    }
}
