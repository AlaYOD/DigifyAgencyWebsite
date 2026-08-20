<?php

use App\Http\Controllers\Admin\JobApplicationCvController;
use App\Http\Controllers\Web\ApplicationController;
use App\Http\Controllers\Web\CareerController;
use App\Http\Controllers\Web\DynamicCareerApplicationController;
use App\Http\Controllers\Web\DynamicFormController;
use App\Http\Controllers\Web\OpenApplicationController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\RedirectFallbackController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Middleware\EnsureTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/ar/', [PageController::class, 'home'])->name('home.ar');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware(EnsureTrailingSlash::class)->group(function (): void {
    Route::get('/careers/', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/open-application/', OpenApplicationController::class)->name('careers.open-application');
    Route::get('/careers/thank-you/', [ApplicationController::class, 'thankYou'])->name('careers.thank-you');
    Route::get('/careers/{slug}/apply/', [ApplicationController::class, 'create'])->name('careers.apply');
    Route::get('/careers/{slug}/', [CareerController::class, 'show'])->name('careers.show');
    Route::get('/ar/careers/', [CareerController::class, 'index'])->name('careers.ar.index');
    Route::get('/ar/careers/open-application/', OpenApplicationController::class)->name('careers.ar.open-application');
    Route::get('/ar/careers/thank-you/', [ApplicationController::class, 'thankYou'])->name('careers.ar.thank-you');
    Route::get('/ar/careers/{slug}/apply/', [ApplicationController::class, 'create'])->name('careers.ar.apply');
    Route::get('/ar/careers/{slug}/', [CareerController::class, 'show'])->name('careers.ar.show');
});

Route::middleware('throttle:5,1')->group(function (): void {
    Route::post('/careers/{slug}/apply', [ApplicationController::class, 'store'])->name('careers.apply.store');
    Route::post('/ar/careers/{slug}/apply', [ApplicationController::class, 'store'])->name('careers.ar.apply.store');
    Route::post('/careers/{slug}/forms/{form:key}/submit', DynamicCareerApplicationController::class)->name('careers.dynamic-apply');
    Route::post('/ar/careers/{slug}/forms/{form:key}/submit', DynamicCareerApplicationController::class)->name('careers.ar.dynamic-apply');
    Route::post('/forms/{form:key}/submit', [DynamicFormController::class, 'store'])->name('forms.submit');
    Route::post('/ar/forms/{form:key}/submit', [DynamicFormController::class, 'store'])->name('forms.ar.submit');
});

Route::get('/admin/job-applications/{application}/cv', JobApplicationCvController::class)
    ->middleware('signed')
    ->name('admin.job-applications.cv');

Route::middleware(EnsureTrailingSlash::class)->group(function (): void {
    Route::get('/insights/{slug}/', [PostController::class, 'show'])->name('posts.show');
    Route::get('/ar/insights/{slug}/', [PostController::class, 'show'])->name('posts.ar.show');
    Route::get('/projects/{slug}/', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/ar/projects/{slug}/', [ProjectController::class, 'show'])->name('projects.ar.show');
    Route::get('/ar/{slug}/', [PageController::class, 'show'])->where('slug', '^(?!admin$|careers$|forms$|insights$|projects$|up$)[^/]+$')->name('pages.ar.show');
    Route::get('/{slug}/', [PageController::class, 'show'])->where('slug', '^(?!admin$|careers$|forms$|insights$|projects$|up$)[^/]+$')->name('pages.show');
});

Route::fallback(RedirectFallbackController::class);
