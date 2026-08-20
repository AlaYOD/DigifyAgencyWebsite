<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use App\Models\JobApplication;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        return [
            Stat::make('Pages', Page::visibleTo($user)->count())->description(Page::visibleTo($user)->where('status', 'draft')->count().' drafts')->icon('heroicon-o-document-text'),
            Stat::make('Articles', Post::visibleTo($user)->count())->description(Post::visibleTo($user)->where('status', 'published')->count().' published')->icon('heroicon-o-newspaper'),
            Stat::make('Projects', Project::visibleTo($user)->count())->description(Project::visibleTo($user)->where('is_featured', true)->count().' featured')->icon('heroicon-o-briefcase'),
            Stat::make('Form submissions', FormSubmission::visibleTo($user)->count())->description(FormSubmission::visibleTo($user)->whereNull('read_at')->count().' unread')->icon('heroicon-o-inbox-arrow-down'),
            Stat::make('Applications', JobApplication::visibleTo($user)->count())->description(JobApplication::visibleTo($user)->where('is_read', false)->count().' unread')->icon('heroicon-o-user-group'),
        ];
    }
}
