<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();
        foreach (Page::published()->get() as $page) {
            $urls->push($this->localizedUrls($page, ''));
        }
        foreach (Post::published()->get() as $post) {
            $urls->push($this->localizedUrls($post, 'insights/'));
        }
        foreach (Project::published()->get() as $project) {
            $urls->push($this->localizedUrls($project, 'projects/'));
        }
        foreach (JobPosting::published()->get() as $job) {
            $urls->push($this->localizedUrls($job, 'careers/'));
        }

        return response()->view('sitemap', ['urls' => $urls->flatten(1)->values()], 200, ['Content-Type' => 'application/xml']);
    }

    private function localizedUrls($model, string $section): array
    {
        return collect(['en', 'ar'])->map(function (string $locale) use ($model, $section): array {
            $slug = $model->getTranslation('slug', $locale, false);
            $prefix = $locale === 'ar' ? 'ar/' : '';

            return ['loc' => url('/'.$prefix.$section.$slug.'/'), 'lastmod' => $model->updated_at?->toDateString()];
        })->filter(fn (array $url): bool => filled(basename(rtrim($url['loc'], '/'))))->values()->all();
    }
}
