<?php

use App\Models\Department;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('allows a manager to create and update postings in their department only', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $engineering = Department::where('slug->en', 'engineering')->firstOrFail();
    $design = Department::where('slug->en', 'design')->firstOrFail();
    $own = JobPosting::factory()->make(['department_id' => $engineering->id]);
    $other = JobPosting::factory()->make(['department_id' => $design->id]);

    expect($manager->can('create', $own))->toBeTrue()
        ->and($manager->can('update', $own))->toBeTrue()
        ->and($manager->can('create', $other))->toBeFalse()
        ->and($manager->can('update', $other))->toBeFalse();
});

it('denies manager publishing and IT creating postings', function () {
    $manager = User::where('email', 'manager@digify.test')->firstOrFail();
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    $posting = JobPosting::factory()->create();

    expect($manager->can('publish', $posting))->toBeFalse()
        ->and($it->can('create', $posting))->toBeFalse();
});

it('allows HR to publish and CEO to publish but not create postings', function () {
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();
    $ceo = User::where('email', 'ceo@digify.test')->firstOrFail();
    $posting = JobPosting::factory()->create();

    expect($hr->can('publish', $posting))->toBeTrue()
        ->and($ceo->can('publish', $posting))->toBeTrue()
        ->and($ceo->can('create', $posting))->toBeFalse();
});
