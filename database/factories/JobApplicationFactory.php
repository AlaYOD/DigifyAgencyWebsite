<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'pipeline_stage_id' => PipelineStage::where('key', 'applied')->value('id'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'locale' => 'en',
            'is_read' => false,
            'applied_at' => now(),
        ];
    }

    public function forDepartment(string $slug): static
    {
        return $this->state(function () use ($slug): array {
            $department = Department::where('slug->en', $slug)->firstOrFail();
            $posting = JobPosting::factory()->create(['department_id' => $department->id]);

            return ['job_posting_id' => $posting->id];
        });
    }
}
