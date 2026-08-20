<?php

namespace App\Providers;

use App\Models\ApplicationNote;
use App\Models\Category;
use App\Models\Department;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PipelineStage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\RedirectMiss;
use App\Models\Revision;
use App\Models\StageTransition;
use App\Models\Tag;
use App\Models\User;
use App\Observers\ContentRevisionObserver;
use App\Observers\JobPostingObserver;
use App\Observers\LocaleObserver;
use App\Policies\ActivityPolicy;
use App\Policies\ApplicationNotePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\FormFieldPolicy;
use App\Policies\FormPolicy;
use App\Policies\FormSubmissionPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPostingPolicy;
use App\Policies\LocalePolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\MenuItemPolicy;
use App\Policies\MenuPolicy;
use App\Policies\PagePolicy;
use App\Policies\PipelineStagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RedirectMissPolicy;
use App\Policies\RedirectPolicy;
use App\Policies\RevisionPolicy;
use App\Policies\StageTransitionPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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
        Page::observe(ContentRevisionObserver::class);
        Post::observe(ContentRevisionObserver::class);
        Project::observe(ContentRevisionObserver::class);
        Locale::observe(LocaleObserver::class);

        Gate::policy(JobPosting::class, JobPostingPolicy::class);
        Gate::policy(JobApplication::class, JobApplicationPolicy::class);
        Gate::policy(StageTransition::class, StageTransitionPolicy::class);
        Gate::policy(ApplicationNote::class, ApplicationNotePolicy::class);
        Gate::policy(PipelineStage::class, PipelineStagePolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(MenuItem::class, MenuItemPolicy::class);
        Gate::policy(Form::class, FormPolicy::class);
        Gate::policy(FormField::class, FormFieldPolicy::class);
        Gate::policy(FormSubmission::class, FormSubmissionPolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);
        Gate::policy(RedirectMiss::class, RedirectMissPolicy::class);
        Gate::policy(Revision::class, RevisionPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Locale::class, LocalePolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
    }
}
