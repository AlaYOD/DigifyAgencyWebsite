<?php

use App\Models\JobApplication;
use App\Models\StageTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('denies update and delete of immutable transitions for every role', function (string $email) {
    $application = JobApplication::factory()->forDepartment('engineering')->create();
    $stage = $application->pipelineStage;
    $transition = StageTransition::create([
        'job_application_id' => $application->id,
        'from_stage_id' => null,
        'to_stage_id' => $stage->id,
        'user_id' => User::where('email', $email)->value('id'),
        'created_at' => now(),
    ]);
    $user = User::where('email', $email)->firstOrFail();

    expect($user->can('update', $transition))->toBeFalse()
        ->and($user->can('delete', $transition))->toBeFalse();
})->with([
    'ceo' => 'ceo@digify.test',
    'manager' => 'manager@digify.test',
    'hr' => 'hr@digify.test',
    'it' => 'it@digify.test',
]);
