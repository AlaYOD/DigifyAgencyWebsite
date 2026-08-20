<?php

namespace App\Filament\Pages;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\StageTransition;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ApplicationsBoard extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Careers';

    protected static ?string $navigationLabel = 'Applications Board';

    protected static ?string $title = 'Applications Board';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected string $view = 'filament.pages.applications-board';

    public array $columns = [];

    public ?int $draggingApplicationId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('applications.view') ?? false;
    }

    public function mount(): void
    {
        $this->loadBoard();
    }

    public function startDragging(int $applicationId): void
    {
        $application = JobApplication::query()
            ->visibleTo(auth()->user())
            ->findOrFail($applicationId);

        abort_unless(auth()->user()->can('move', $application), 403);

        $this->draggingApplicationId = $applicationId;
    }

    public function moveApplication(int $applicationId, int $toStageId): void
    {
        $application = JobApplication::query()
            ->visibleTo(auth()->user())
            ->with('pipelineStage')
            ->findOrFail($applicationId);

        abort_unless(auth()->user()->can('move', $application), 403);

        $fromStageId = (int) $application->pipeline_stage_id;

        if ($fromStageId === $toStageId) {
            $this->draggingApplicationId = null;

            return;
        }

        DB::transaction(function () use ($application, $fromStageId, $toStageId): void {
            StageTransition::create([
                'job_application_id' => $application->id,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $toStageId,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);

            $application->update(['pipeline_stage_id' => $toStageId]);
        });

        $card = null;
        foreach ($this->columns as &$column) {
            foreach ($column['applications'] as $index => $existingCard) {
                if ((int) $existingCard['id'] === $application->id) {
                    $card = $existingCard;
                    unset($column['applications'][$index]);
                    $column['applications'] = array_values($column['applications']);
                    $column['count'] = count($column['applications']);
                    break 2;
                }
            }
        }
        unset($column);

        if ($card !== null) {
            $card['pipeline_stage_id'] = $toStageId;
            foreach ($this->columns as &$column) {
                if ((int) $column['stage']['id'] === $toStageId) {
                    $column['applications'][] = $card;
                    $column['count'] = count($column['applications']);
                    break;
                }
            }
            unset($column);
        }

        $this->draggingApplicationId = null;
    }

    public function applicationUrl(int $applicationId): string
    {
        return JobApplicationResource::getUrl('edit', ['record' => $applicationId]);
    }

    private function loadBoard(): void
    {
        $stages = PipelineStage::ordered()->get();
        $applications = JobApplication::query()
            ->visibleTo(auth()->user())
            ->with(['jobPosting', 'pipelineStage'])
            ->get();

        $this->columns = $stages->map(function (PipelineStage $stage) use ($applications): array {
            $cards = $applications
                ->where('pipeline_stage_id', $stage->id)
                ->map(fn (JobApplication $application): array => [
                    'id' => $application->id,
                    'display_name' => auth()->user()->can('viewPii', $application)
                        ? $application->display_name
                        : "Candidate #{$application->id}",
                    'reference_code' => $application->jobPosting->reference_code,
                    'ai_score' => $application->ai_score,
                    'rating' => $application->rating,
                    'applied_at' => $application->applied_at?->diffForHumans(),
                    'pipeline_stage_id' => $application->pipeline_stage_id,
                    'can_drag' => auth()->user()->can('move', $application),
                ])->values()->all();

            return [
                'stage' => [
                    'id' => $stage->id,
                    'name' => $stage->getTranslation('name', app()->getLocale()),
                    'color' => $stage->color,
                ],
                'count' => count($cards),
                'applications' => $cards,
            ];
        })->all();
    }
}
