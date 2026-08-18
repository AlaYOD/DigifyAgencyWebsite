<?php

use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
    Storage::fake('private');
});

function submissionPayload(string $email = 'candidate@example.com', string $filename = 'cv.pdf', string $mime = 'application/pdf'): array
{
    return [
        'first_name' => 'Layla',
        'last_name' => 'Haddad',
        'email' => $email,
        'phone' => '+970 59 123 4567',
        'cover_letter' => 'I would love to contribute.',
        'portfolio_url' => 'https://example.com/portfolio',
        'linkedin_url' => 'https://linkedin.com/in/candidate',
        'cv' => $mime === 'application/pdf'
            ? UploadedFile::fake()->createWithContent($filename, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF")
            : UploadedFile::fake()->create($filename, 100, $mime),
    ];
}

function publishedApplicationJob(array $attributes = []): JobPosting
{
    return JobPosting::factory()->create(array_merge([
        'status' => 'published',
        'published_at' => now()->subHour(),
        'closes_at' => now()->addDay(),
    ], $attributes));
}

it('creates an Arabic application in the default stage with a private CV', function () {
    $job = publishedApplicationJob([
        'slug' => ['en' => 'arabic-test-job', 'ar' => 'وظيفة-اختبار'],
    ]);

    $response = $this->post('/ar/careers/'.rawurlencode('وظيفة-اختبار').'/apply', submissionPayload());

    $response->assertRedirect('/ar/careers/thank-you/');
    $application = JobApplication::where('email', 'candidate@example.com')->firstOrFail();
    $media = $application->getFirstMedia('cv');

    expect($application->job_posting_id)->toBe($job->id)
        ->and($application->pipelineStage->is_default)->toBeTrue()
        ->and($application->locale)->toBe('ar')
        ->and($media)->not->toBeNull()
        ->and(Storage::disk('private')->exists($media->getPathRelativeToRoot()))->toBeTrue()
        ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot()))->toBeFalse();
});

it('rejects a duplicate email on the same vacancy with its reference', function () {
    $job = publishedApplicationJob();
    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload())->assertRedirect();

    $response = $this->from('/careers/'.$job->getTranslation('slug', 'en').'/apply/')
        ->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload());

    $response->assertRedirect()
        ->assertSessionHasErrors('email');
    expect(session('errors')->get('email')[0])->toContain($job->reference_code)
        ->and(JobApplication::where('job_posting_id', $job->id)->where('email', 'candidate@example.com')->count())->toBe(1);
});

it('allows the same email on a different vacancy', function () {
    $first = publishedApplicationJob();
    $second = publishedApplicationJob();
    $this->post('/careers/'.$first->getTranslation('slug', 'en').'/apply', submissionPayload())->assertRedirect();
    $this->post('/careers/'.$second->getTranslation('slug', 'en').'/apply', submissionPayload())->assertRedirect();

    expect(JobApplication::where('email', 'candidate@example.com')->count())->toBe(2);
});

it('returns 404 for a closed vacancy', function () {
    $job = JobPosting::factory()->create(['status' => 'closed']);

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload())
        ->assertNotFound();
});

it('rejects an executable renamed to PDF by MIME type', function () {
    $job = publishedApplicationJob();

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload('exe@example.com', 'malware.pdf', 'application/x-msdownload'))
        ->assertSessionHasErrors('cv');
    expect(JobApplication::where('email', 'exe@example.com')->exists())->toBeFalse();
});

it('rejects an 11MB CV', function () {
    $job = publishedApplicationJob();

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', array_merge(
        submissionPayload('large@example.com'),
        ['cv' => UploadedFile::fake()->create('large.pdf', 11264, 'application/pdf')],
    ))->assertSessionHasErrors('cv');
});

it('silently discards a filled honeypot with HTTP 200', function () {
    $job = publishedApplicationJob();

    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', ['website' => 'https://bot.test'])
        ->assertOk();

    expect(JobApplication::count())->toBe(0);
});

it('rate limits the sixth submission from one IP in a minute', function () {
    $job = publishedApplicationJob();
    RateLimiter::clear('10.20.30.40');

    for ($index = 1; $index <= 6; $index++) {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload('rate-'.$index.'@example.com'));

        if ($index < 6) {
            $response->assertRedirect();
        } else {
            $response->assertStatus(429);
        }
    }
});

it('appears in the HR admin application query', function () {
    $job = publishedApplicationJob();
    $this->post('/careers/'.$job->getTranslation('slug', 'en').'/apply', submissionPayload('hr-list@example.com'))->assertRedirect();
    $application = JobApplication::where('email', 'hr-list@example.com')->firstOrFail();
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();

    Auth::login($hr);

    expect(JobApplicationResource::getEloquentQuery()->whereKey($application->id)->exists())->toBeTrue();
});
