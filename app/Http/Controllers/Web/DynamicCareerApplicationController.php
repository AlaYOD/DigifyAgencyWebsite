<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicFormRequest;
use App\Models\Form;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use App\Services\CaptchaVerifier;
use App\Services\FormSubmissionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DynamicCareerApplicationController extends Controller
{
    public function __invoke(DynamicFormRequest $request, string $slug, Form $form, FormSubmissionService $submissions, CaptchaVerifier $captcha): RedirectResponse
    {
        $job = JobPosting::published()->where('slug->'.app()->getLocale(), $slug)->where('form_id', $form->id)->firstOrFail();

        if (filled($request->input('_website'))) {
            return back()->with('form_success', $form->getTranslation('success_message', app()->getLocale()));
        }

        if ($form->captcha_enabled && ! $captcha->verify((string) $request->input('captcha_token'), $request->ip())) {
            throw ValidationException::withMessages(['captcha_token' => __('Captcha verification failed. Please try again.')]);
        }

        $validated = $request->validated();
        $email = strtolower(trim((string) ($validated['email'] ?? '')));
        [$firstName, $lastName] = $this->names($validated);

        if ($email === '' || $firstName === '' || $lastName === '') {
            throw ValidationException::withMessages(['email' => __('Career forms require email and candidate name fields.')]);
        }

        if (JobApplication::query()->where('job_posting_id', $job->id)->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => __('An application for this vacancy already exists. Reference: :reference', ['reference' => $job->reference_code])]);
        }

        try {
            $submission = $submissions->submit($form, $validated, (string) $request->ip(), $request->userAgent(), $request->headers->get('referer'));
            $application = DB::transaction(function () use ($job, $submission, $validated, $email, $firstName, $lastName): JobApplication {
                $application = JobApplication::create([
                    'job_posting_id' => $job->id, 'form_submission_id' => $submission?->id,
                    'pipeline_stage_id' => PipelineStage::where('is_default', true)->value('id'),
                    'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email,
                    'phone' => $validated['phone'] ?? null, 'cover_letter' => $validated['cover_letter'] ?? $validated['introduction'] ?? null,
                    'portfolio_url' => $validated['portfolio_url'] ?? null, 'linkedin_url' => $validated['linkedin_url'] ?? null,
                    'locale' => app()->getLocale(), 'source' => 'dynamic-form', 'applied_at' => now(),
                ]);

                $cvPath = $submission ? data_get($submission->data, 'cv') : null;
                if (is_string($cvPath) && Storage::disk('private')->exists($cvPath)) {
                    $application->addMediaFromDisk($cvPath, 'private')->toMediaCollection('cv');
                }

                $job->increment('applications_count');

                return $application;
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }
            throw ValidationException::withMessages(['email' => __('An application for this vacancy already exists. Reference: :reference', ['reference' => $job->reference_code])]);
        }

        return redirect(app()->getLocale() === 'ar' ? route('careers.ar.thank-you') : route('careers.thank-you'))
            ->with('application_reference', $application->jobPosting->reference_code);
    }

    private function names(array $validated): array
    {
        if (filled($validated['first_name'] ?? null) && filled($validated['last_name'] ?? null)) {
            return [trim($validated['first_name']), trim($validated['last_name'])];
        }

        $parts = preg_split('/\s+/', trim((string) ($validated['full_name'] ?? '')), 2) ?: [];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
