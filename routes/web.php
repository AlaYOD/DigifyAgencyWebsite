<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\JobApplicationCvController;
use App\Http\Controllers\Web\CareerController;
use App\Http\Controllers\Web\ApplicationController;
use App\Http\Middleware\EnsureTrailingSlash;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(EnsureTrailingSlash::class)->group(function (): void {
    Route::get('/careers/', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/thank-you/', [ApplicationController::class, 'thankYou'])->name('careers.thank-you');
    Route::get('/careers/{slug}/apply/', [ApplicationController::class, 'create'])->name('careers.apply');
    Route::get('/careers/{slug}/', [CareerController::class, 'show'])->name('careers.show');
    Route::get('/ar/careers/', [CareerController::class, 'index'])->name('careers.ar.index');
    Route::get('/ar/careers/thank-you/', [ApplicationController::class, 'thankYou'])->name('careers.ar.thank-you');
    Route::get('/ar/careers/{slug}/apply/', [ApplicationController::class, 'create'])->name('careers.ar.apply');
    Route::get('/ar/careers/{slug}/', [CareerController::class, 'show'])->name('careers.ar.show');
});

Route::middleware('throttle:5,1')->group(function (): void {
    Route::post('/careers/{slug}/apply', [ApplicationController::class, 'store'])->name('careers.apply.store');
    Route::post('/ar/careers/{slug}/apply', [ApplicationController::class, 'store'])->name('careers.ar.apply.store');
});

Route::get('/admin/job-applications/{application}/cv', JobApplicationCvController::class)
    ->middleware('signed')
    ->name('admin.job-applications.cv');
