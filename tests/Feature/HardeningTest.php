<?php

use App\Models\Department;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use App\Models\StageTransition;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('records both concurrent stage moves as immutable transitions', function (): void {
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $manager = User::factory()->manager(department: 'engineering')->create();
    $screening = PipelineStage::where('key', 'screening')->firstOrFail();
    $interview = PipelineStage::where('key', 'interview')->firstOrFail();

    foreach ([$screening, $interview] as $stage) {
        StageTransition::create([
            'job_application_id' => $application->id,
            'from_stage_id' => $application->pipeline_stage_id,
            'to_stage_id' => $stage->id,
            'user_id' => $manager->id,
            'created_at' => now(),
        ]);
        $application->update(['pipeline_stage_id' => $stage->id]);
    }

    expect($application->transitions()->count())->toBe(2);
});

it('generates distinct reference codes for postings created in one department', function (): void {
    $departmentId = Department::where('slug->en', 'engineering')->value('id');

    $first = JobPosting::factory()->create(['department_id' => $departmentId]);
    $second = JobPosting::factory()->create(['department_id' => $departmentId]);

    expect($first->reference_code)->not->toBe($second->reference_code);
});

it('cascades an application when its vacancy is deleted', function (): void {
    $job = JobPosting::factory()->create();
    $application = JobApplication::factory()->create(['job_posting_id' => $job->id]);

    $job->forceDelete();

    expect(JobApplication::withTrashed()->find($application->id))->toBeNull();
});

it('rejects submission when a vacancy closes after the form was loaded', function (): void {
    $job = JobPosting::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
        'closes_at' => now()->subSecond(),
    ]);

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', [
        'first_name' => 'Rana',
        'last_name' => 'Saleh',
        'email' => 'closed@example.com',
        'cv' => UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n%%EOF"),
    ])->assertNotFound();
});

it('rejects an expired CV signed URL', function (): void {
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $url = URL::temporarySignedRoute(
        'admin.job-applications.cv',
        now()->subMinute(),
        ['application' => $application],
    );

    $this->actingAs($hr)->get($url)->assertForbidden();
});

it('does not allow an inactive user to access the admin panel', function (): void {
    $user = User::factory()->create(['is_active' => false]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});
