<?php

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('gives a roleless user an empty application result set without throwing', function () {
    $user = User::factory()->create();
    JobApplication::factory()->forDepartment('engineering')->create();

    expect(JobApplication::visibleTo($user)->count())->toBe(0)
        ->and($user->can('viewAny', JobApplication::class))->toBeFalse();
});

it('returns zero visible postings and false policy checks for a roleless user', function () {
    $user = User::factory()->create();
    $posting = JobPosting::factory()->create();

    expect(JobPosting::visibleTo($user)->count())->toBe(0)
        ->and($user->can('viewAny', JobPosting::class))->toBeFalse()
        ->and($user->can('view', $posting))->toBeFalse()
        ->and($user->can('create', $posting))->toBeFalse()
        ->and($user->can('publish', $posting))->toBeFalse();
});
