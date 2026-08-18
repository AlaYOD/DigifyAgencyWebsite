<?php

namespace App\Providers;

use App\Models\JobPosting;
use App\Models\ApplicationNote;
use App\Models\Department;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\StageTransition;
use App\Models\User;
use App\Observers\JobPostingObserver;
use App\Policies\ApplicationNotePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPostingPolicy;
use App\Policies\PipelineStagePolicy;
use App\Policies\StageTransitionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') && config('mail.default') === 'log') {
            throw new \RuntimeException('The log mailer is forbidden in production. Configure Postmark or Resend.');
        }

        JobPosting::observe(JobPostingObserver::class);

        Gate::policy(JobPosting::class, JobPostingPolicy::class);
        Gate::policy(JobApplication::class, JobApplicationPolicy::class);
        Gate::policy(StageTransition::class, StageTransitionPolicy::class);
        Gate::policy(ApplicationNote::class, ApplicationNotePolicy::class);
        Gate::policy(PipelineStage::class, PipelineStagePolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
