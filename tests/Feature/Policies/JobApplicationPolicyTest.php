<?php

use App\Models\Department;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

function expectForbidden($test, $user, string $ability, $model): void
{
    $path = '/_policy-test/'.Str::uuid();
    Route::get($path, fn () => Gate::forUser($user)->authorize($ability, $model));
    $test->actingAs($user)->get($path)->assertForbidden();
}

it('allows a manager to view an application in a managed department', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $application = JobApplication::factory()->forDepartment('engineering')->create();

    expect($manager->can('view', $application))->toBeTrue();
});

it('denies a manager viewing an application in another department with 403', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $application = JobApplication::factory()->forDepartment('design')->create();
    expectForbidden($this, $manager, 'view', $application);
});

it('returns zero visible applications from another department for a manager', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    JobApplication::factory()->forDepartment('design')->count(2)->create();

    expect(JobApplication::visibleTo($manager)->count())->toBe(0);
});

it('limits a manager with two departments to exactly both departments', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $design = Department::where('slug->en', 'design')->firstOrFail();
    $marketing = Department::where('slug->en', 'marketing')->firstOrFail();
    $manager->managedDepartments()->sync([$manager->department_id, $design->id]);

    JobApplication::factory()->forDepartment('engineering')->create();
    JobApplication::factory()->forDepartment('design')->create();
    JobApplication::factory()->forDepartment('marketing')->create();

    expect(JobApplication::visibleTo($manager)->pluck('job_posting_id')->count())->toBe(2)
        ->and($manager->managedDepartments()->pluck('departments.id')->all())
        ->toEqualCanonicalizing([$manager->department_id, $design->id])
        ->and($marketing->id)->not->toBeIn($manager->managedDepartments()->pluck('departments.id')->all());
});

it('allows viewing application data but denies PII to IT', function () {
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();

    expect($it->can('view', $application))->toBeTrue()
        ->and($it->can('viewPii', $application))->toBeFalse();
});

it('lets IT see all applications while keeping PII denied', function () {
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    JobApplication::factory()->forDepartment('engineering')->create();
    JobApplication::factory()->forDepartment('design')->create();

    expect(JobApplication::visibleTo($it)->count())->toBe(2)
        ->and($it->can('viewPii', JobApplication::visibleTo($it)->first()))->toBeFalse();
});

it('denies IT a direct CV download authorization with 403', function () {
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    expectForbidden($this, $it, 'viewPii', $application);
});

it('returns 403 when IT requests the signed CV URL directly', function () {
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $url = URL::temporarySignedRoute(
        'admin.job-applications.cv',
        now()->addMinutes(15),
        ['application' => $application],
    );

    $this->actingAs($it)->get($url)->assertForbidden();
});

it('allows CEO and HR to view application PII across departments', function () {
    $application = JobApplication::factory()->forDepartment('design')->create();
    $ceo = User::where('email', 'ceo@digify.test')->firstOrFail();
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();

    expect($ceo->can('viewPii', $application))->toBeTrue()
        ->and($hr->can('viewPii', $application))->toBeTrue();
});

it('denies manager export and CEO delete while allowing HR delete', function () {
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $manager = User::where('email', 'manager@digify.test')->firstOrFail();
    $ceo = User::where('email', 'ceo@digify.test')->firstOrFail();
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();

    expect($manager->can('export', $application))->toBeFalse()
        ->and($ceo->can('delete', $application))->toBeFalse()
        ->and($hr->can('delete', $application))->toBeTrue();
});

it('allows a manager to move an application in scope but not outside scope', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $engineering = JobApplication::factory()->forDepartment('engineering')->create();
    $design = JobApplication::factory()->forDepartment('design')->create();

    expect($manager->can('move', $engineering))->toBeTrue()
        ->and($manager->can('move', $design))->toBeFalse();
});
