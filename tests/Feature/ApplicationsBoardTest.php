<?php

use App\Filament\Pages\ApplicationsBoard;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\StageTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('shows all applications to IT while disabling drag', function () {
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    JobApplication::factory()->forDepartment('engineering')->create();
    JobApplication::factory()->forDepartment('design')->create();

    Auth::login($it);
    $board = new ApplicationsBoard;
    $board->mount();

    $cards = collect($board->columns)->flatMap(fn (array $column): array => $column['applications']);

    expect($cards->count())->toBe(2)
        ->and($cards->every(fn (array $card): bool => $card['can_drag'] === false))->toBeTrue();
});

it('limits a manager board to managed departments', function () {
    $manager = User::where('email', 'manager@digify.test')->firstOrFail();
    JobApplication::factory()->forDepartment('engineering')->create();
    JobApplication::factory()->forDepartment('design')->create();

    Auth::login($manager);
    $board = new ApplicationsBoard;
    $board->mount();

    $cards = collect($board->columns)->flatMap(fn (array $column): array => $column['applications']);

    expect($cards->count())->toBe(1)
        ->and($cards->every(fn (array $card): bool => $card['can_drag'] === true))->toBeTrue();
});

it('writes one immutable transition per changed drag', function () {
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $screening = PipelineStage::where('key', 'screening')->value('id');
    $interview = PipelineStage::where('key', 'interview')->value('id');

    Auth::login($hr);
    $board = new ApplicationsBoard;
    $board->mount();
    $board->moveApplication($application->id, $screening);
    $board->moveApplication($application->id, $interview);

    expect(StageTransition::where('job_application_id', $application->id)->count())->toBe(2)
        ->and(StageTransition::where('job_application_id', $application->id)->pluck('user_id')->all())
        ->toBe([$hr->id, $hr->id]);
});

it('writes no transition when a drag keeps the same stage', function () {
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();
    $application = JobApplication::factory()->forDepartment('engineering')->create();

    Auth::login($hr);
    $board = new ApplicationsBoard;
    $board->mount();
    $board->moveApplication($application->id, $application->pipeline_stage_id);

    expect(StageTransition::where('job_application_id', $application->id)->count())->toBe(0);
});

it('loads a board with a constant eager-loaded query count', function () {
    $hr = User::where('email', 'hr@digify.test')->firstOrFail();
    JobApplication::factory()->forDepartment('engineering')->count(20)->create();

    Auth::login($hr);
    DB::enableQueryLog();
    $board = new ApplicationsBoard;
    $board->mount();

    expect(collect($board->columns)->sum('count'))->toBe(20)
        ->and(count(DB::getQueryLog()))->toBeLessThanOrEqual(10);
});
