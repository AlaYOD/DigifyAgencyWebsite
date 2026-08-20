<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProjectResource;
use App\Models\Project;
use App\Services\BlockResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(Request $request, string $slug, BlockResolver $resolver): Response
    {
        $locale = app()->getLocale();
        $project = Project::published()->where("slug->{$locale}", $slug)->firstOrFail();

        return Inertia::render('Projects/Show', [
            'project' => (new PublicProjectResource($project))->resolve($request),
            'blocks' => $resolver->resolve($project->blocks ?? [], $locale),
        ]);
    }
}
