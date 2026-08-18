<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\CareerPostingResource;
use App\Mail\ApplicationReceived;
use App\Mail\NewApplication;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function create(string $slug): Response
    {
        $job = $this->publishedJob($slug);

        return Inertia::render('Careers/Apply', [
            'job' => CareerPostingResource::make($job),
        ]);
    }

    public function store(StoreApplicationRequest $request, string $slug): RedirectResponse|\Illuminate\Http\Response
    {
        // This fixed form is temporary; the dynamic forms engine will replace it later.
        if ($request->filled('website')) {
            return response('', 200);
        }

        $job = $this->publishedJob($slug);
        $validated = $request->validated();
        $validated['email'] = strtolower(trim($validated['email']));

        $existing = JobApplication::query()
            ->where('job_posting_id', $job->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing !== null) {
            return back()->withErrors([
                'email' => 'An application for this vacancy already exists. Reference: '.$job->reference_code,
            ]);
        }

        try {
            $application = DB::transaction(function () use ($job, $validated, $request): JobApplication {
                $application = JobApplication::create([
                    'job_posting_id' => $job->id,
                    'pipeline_stage_id' => PipelineStage::where('is_default', true)->value('id'),
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'cover_letter' => $validated['cover_letter'] ?? null,
                    'portfolio_url' => $validated['portfolio_url'] ?? null,
                    'linkedin_url' => $validated['linkedin_url'] ?? null,
                    'locale' => app()->getLocale(),
                    'source' => 'website',
                    'applied_at' => now(),
                ]);

                $application->addMedia($request->file('cv'))->toMediaCollection('cv');
                $job->increment('applications_count');

                return $application;
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }

            $existing = JobApplication::query()
                ->where('job_posting_id', $job->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existing === null) {
                throw $exception;
            }

            return back()->withErrors([
                'email' => 'An application for this vacancy already exists. Reference: '.$existing->jobPosting->reference_code,
            ]);
        }

        $thankYouRoute = app()->getLocale() === 'ar'
            ? route('careers.ar.thank-you')
            : route('careers.thank-you');

        foreach (config('mail.hr_recipients', []) as $recipient) {
            $this->queueMail($recipient, new NewApplication($application));
        }
        $this->queueMail($application->email, new ApplicationReceived($application));

        return redirect($thankYouRoute)->with('application_reference', $application->jobPosting->reference_code);
    }

    public function thankYou(Request $request): Response
    {
        return Inertia::render('Careers/ThankYou', [
            'referenceCode' => $request->session()->get('application_reference'),
        ]);
    }

    private function publishedJob(string $slug): JobPosting
    {
        return JobPosting::query()
            ->published()
            ->where('slug->'.app()->getLocale(), $slug)
            ->firstOrFail();
    }

    private function queueMail(string $recipient, \Illuminate\Mail\Mailable $mail): void
    {
        try {
            Mail::to($recipient)->queue($mail);
        } catch (\Throwable $exception) {
            Log::error('Application email dispatch failed.', [
                'application_id' => $mail->application->id,
                'recipient' => $recipient,
                'exception' => $exception,
            ]);
        }
    }
}
