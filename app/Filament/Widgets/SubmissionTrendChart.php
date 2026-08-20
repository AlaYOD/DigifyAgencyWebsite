<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;

class SubmissionTrendChart extends ChartWidget
{
    protected ?string $heading = 'Inbound activity — last 14 days';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $daysAgo) => today()->subDays($daysAgo));
        $user = auth()->user();

        return [
            'datasets' => [
                ['label' => 'Form submissions', 'data' => $days->map(fn ($day): int => FormSubmission::visibleTo($user)->whereDate('created_at', $day)->count())->all(), 'borderColor' => '#1f75fd', 'backgroundColor' => '#1f75fd33'],
                ['label' => 'Job applications', 'data' => $days->map(fn ($day): int => JobApplication::visibleTo($user)->whereDate('applied_at', $day)->count())->all(), 'borderColor' => '#27c362', 'backgroundColor' => '#27c36233'],
            ],
            'labels' => $days->map(fn ($day): string => $day->format('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
