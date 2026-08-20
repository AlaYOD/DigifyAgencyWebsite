<?php

use App\Models\Category;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\RedirectMiss;
use App\Models\Revision;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('denies every CMS model to a roleless user', function (): void {
    $user = User::factory()->create();
    $models = [
        Page::class, Post::class, Project::class, Category::class, Tag::class,
        Menu::class, MenuItem::class, Form::class, FormField::class,
        FormSubmission::class, Redirect::class, RedirectMiss::class,
        Revision::class, Locale::class, MediaAsset::class, Activity::class,
    ];

    foreach ($models as $model) {
        expect($user->can('viewAny', $model))->toBeFalse("Roleless user unexpectedly accessed {$model}");
    }
});

it('keeps department-scoped CMS queries deny by default', function (): void {
    $user = User::factory()->create();

    expect(Page::visibleTo($user)->count())->toBe(0)
        ->and(Post::visibleTo($user)->count())->toBe(0)
        ->and(Project::visibleTo($user)->count())->toBe(0)
        ->and(Form::visibleTo($user)->count())->toBe(0)
        ->and(FormSubmission::visibleTo($user)->count())->toBe(0);
});
