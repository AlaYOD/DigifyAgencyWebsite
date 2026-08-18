<?php

use App\Mail\ApplicationReceived;
use App\Mail\NewApplication;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
    Storage::fake('private');
});

function mailApplicationPayload(string $email = 'mail@example.com'): array
{
    return [
        'first_name' => 'Rana',
        'last_name' => 'Saleh',
        'email' => $email,
        'cv' => UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n%%EOF"),
    ];
}

function mailPublishedJob(array $attributes = []): JobPosting
{
    return JobPosting::factory()->create(array_merge([
        'status' => 'published',
        'published_at' => now()->subHour(),
        'closes_at' => now()->addDay(),
    ], $attributes));
}

it('queues candidate and HR email in the application locale', function (): void {
    Mail::fake();
    $job = mailPublishedJob(['slug' => ['en' => 'mail-job', 'ar' => 'وظيفة-بريد']]);

    $this->post('/ar/careers/'.rawurlencode('وظيفة-بريد').'/apply', mailApplicationPayload())
        ->assertRedirect();

    $application = JobApplication::firstOrFail();
    Mail::assertQueued(ApplicationReceived::class, fn (ApplicationReceived $mail): bool =>
        $mail->application->is($application) && $mail->application->locale === 'ar');
    Mail::assertQueued(NewApplication::class, fn (NewApplication $mail): bool =>
        $mail->application->is($application));
});

it('renders the actual Arabic vacancy title in the candidate email', function (): void {
    $arabicTitle = "\u{0645}\u{0647}\u{0646}\u{062F}\u{0633} \u{0628}\u{0631}\u{0645}\u{062C}\u{064A}\u{0627}\u{062A}";
    $job = mailPublishedJob(['title' => ['en' => 'Software Engineer', 'ar' => $arabicTitle]]);
    $application = JobApplication::factory()->create([
        'job_posting_id' => $job->id,
        'pipeline_stage_id' => PipelineStage::where('is_default', true)->value('id'),
        'locale' => 'ar',
    ]);

    expect((new ApplicationReceived($application))->render())
        ->toContain($arabicTitle)
        ->not->toContain("\u{0648}\u{0638}\u{064A}\u{0641}\u{0629} \u{0634}\u{0627}\u{063A}\u{0631}\u{0629}");
});

it('persists an application when mail dispatch fails', function (): void {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('provider unavailable'));
    $job = mailPublishedJob();

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', mailApplicationPayload('saved@example.com'))
        ->assertRedirect();

    expect(JobApplication::where('email', 'saved@example.com')->exists())->toBeTrue();
});

it('closes expired published vacancies and leaves active ones alone', function (): void {
    $expired = mailPublishedJob(['closes_at' => now()->subMinute()]);
    $active = mailPublishedJob(['closes_at' => now()->addHour()]);
    $openEnded = mailPublishedJob(['closes_at' => null]);

    $this->artisan('careers:close-expired')->assertSuccessful();

    expect($expired->refresh()->status->value)->toBe('closed')
        ->and($active->refresh()->status->value)->toBe('published')
        ->and($openEnded->refresh()->status->value)->toBe('published');
});
