<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicPageResource;
use App\Models\Page;
use App\Services\BlockResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(Request $request, BlockResolver $resolver): Response
    {
        $page = Page::published()->where('is_homepage', true)->first();

        if (! $page) {
            return Inertia::render('Pages/Show', [
                'page' => ['title' => 'Digify', 'excerpt' => 'Digital experiences with impact', 'seo' => null, 'template' => 'default'],
                'blocks' => [],
            ]);
        }

        return $this->render($request, $page, $resolver);
    }

    public function show(Request $request, string $slug, BlockResolver $resolver): Response
    {
        $locale = app()->getLocale();
        $page = Page::published()->where("slug->{$locale}", $slug)->firstOrFail();

        return $this->render($request, $page, $resolver);
    }

    private function render(Request $request, Page $page, BlockResolver $resolver): Response
    {
        return Inertia::render('Pages/Show', [
            'page' => (new PublicPageResource($page))->resolve($request),
            'blocks' => $resolver->resolve($page->blocks ?? [], app()->getLocale()),
        ]);
    }
}
