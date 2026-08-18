<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareerPostingResource;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends Controller
{
    public function index(Request $request): Response
    {
        $jobs = JobPosting::query()
            ->published()
            ->with('department')
            ->when($request->string('employment_type')->isNotEmpty(), fn ($query) => $query->where('employment_type', $request->string('employment_type')))
            ->when($request->string('workplace_type')->isNotEmpty(), fn ($query) => $query->where('workplace_type', $request->string('workplace_type')))
            ->when($request->string('department')->isNotEmpty(), fn ($query) => $query->whereHas('department', fn ($department) => $department->where('slug->en', $request->string('department'))))
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->get();

        return Inertia::render('Careers/Index', [
            'jobs' => CareerPostingResource::collection($jobs),
            'filters' => $request->only(['employment_type', 'workplace_type', 'department']),
        ]);
    }

    public function show(string $slug): Response
    {
        $job = JobPosting::query()
            ->published()
            ->with('department')
            ->where('slug->'.app()->getLocale(), $slug)
            ->firstOrFail();

        $response = Inertia::render('Careers/Show', [
            'job' => CareerPostingResource::make($job),
        ]);

        return $response->withViewData([
            'careerJsonLd' => CareerPostingResource::make($job)->resolve(request())['json_ld'],
        ]);
    }
}
