<?php

namespace App\Services;

use App\Http\Resources\PublicFormResource;
use App\Models\Form;
use App\Models\JobPosting;
use App\Models\Post;
use App\Models\Project;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlockResolver
{
    public function resolve(array $blocks, string $locale): array
    {
        return collect($blocks)->values()->map(function (array $block, int $index) use ($locale): array {
            $type = $block['type'] ?? 'unknown';
            $data = $this->localize($block['data'] ?? [], $locale);

            $data = match ($type) {
                'case_reel' => $this->resolveProjects($data, $locale),
                'posts_grid' => $this->resolvePosts($data, $locale),
                'jobs_list' => $this->resolveJobs($data, $locale),
                'form' => $this->resolveForm($data),
                default => $data,
            };

            if (filled($data['media_id'] ?? null)) {
                $data['media'] = $this->resolveMedia((int) $data['media_id']);
                unset($data['media_id']);
            }

            if (filled($data['media_ids'] ?? null)) {
                $data['media'] = Media::query()->whereIn('id', array_map('intval', $data['media_ids']))->get()->map(fn (Media $media): array => $this->shapeMedia($media))->all();
                unset($data['media_ids']);
            }

            if ($type === 'rich_text' && is_string($data['content'] ?? null)) {
                $data['content'] = app(HtmlSanitizer::class)->sanitize($data['content']);
            }

            return ['id' => "block-{$index}", 'type' => $type, 'props' => $data];
        })->all();
    }

    private function resolveProjects(array $data, string $locale): array
    {
        $ids = array_map('intval', $data['project_ids'] ?? []);
        $projects = Project::published()->whereIn('id', $ids)->get()->sortBy(fn (Project $project): int => array_search($project->id, $ids, true));
        $data['projects'] = $projects->map(fn (Project $project): array => [
            'id' => $project->id, 'slug' => $project->getTranslation('slug', $locale), 'title' => $project->getTranslation('title', $locale),
            'summary' => $project->getTranslation('summary', $locale), 'client_name' => $project->client_name, 'sector' => $project->sector, 'year' => $project->year,
        ])->values()->all();
        unset($data['project_ids']);

        return $data;
    }

    private function resolvePosts(array $data, string $locale): array
    {
        $posts = Post::published()->with('category')->when($data['category_id'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($data['featured_only'] ?? false, fn ($query) => $query->where('is_featured', true))->latest('published_at')->limit((int) ($data['limit'] ?? 6))->get();
        $data['posts'] = $posts->map(fn (Post $post): array => [
            'id' => $post->id, 'slug' => $post->getTranslation('slug', $locale), 'title' => $post->getTranslation('title', $locale),
            'excerpt' => $post->getTranslation('excerpt', $locale), 'published_at' => $post->published_at?->toDateString(),
            'category' => $post->category?->getTranslation('name', $locale),
        ])->all();

        return $data;
    }

    private function resolveJobs(array $data, string $locale): array
    {
        $jobs = JobPosting::published()->with('department')->when($data['department_id'] ?? null, fn ($query, $department) => $query->where('department_id', $department))
            ->latest('published_at')->limit((int) ($data['limit'] ?? 12))->get();
        $data['jobs'] = $jobs->map(fn (JobPosting $job): array => [
            'id' => $job->id, 'slug' => $job->getTranslation('slug', $locale), 'title' => $job->getTranslation('title', $locale),
            'summary' => $job->getTranslation('summary', $locale), 'employment_type' => $job->employment_type->value,
            'workplace_type' => $job->workplace_type->value, 'department' => $job->department?->getTranslation('name', $locale),
        ])->all();

        return $data;
    }

    private function resolveForm(array $data): array
    {
        $form = Form::query()->where('is_active', true)->with('fields')->find($data['form_id'] ?? null);
        $data['form'] = $form ? (new PublicFormResource($form))->resolve(request()) : null;
        unset($data['form_id']);

        return $data;
    }

    private function resolveMedia(int $id): ?array
    {
        $media = Media::query()->find($id);

        return $media ? $this->shapeMedia($media) : null;
    }

    private function shapeMedia(Media $media): array
    {
        return ['id' => $media->id, 'url' => $media->getUrl(), 'mime_type' => $media->mime_type, 'name' => $media->name];
    }

    private function localize(mixed $value, string $locale): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_key_exists($locale, $value) || array_key_exists('en', $value)) {
            return $value[$locale] ?? $value['en'] ?? null;
        }

        return collect($value)->map(fn ($item) => $this->localize($item, $locale))->all();
    }
}
