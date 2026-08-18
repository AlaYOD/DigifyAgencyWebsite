<?php

namespace App\Http\Controllers\Admin;

use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class JobApplicationCvController
{
    public function __invoke(JobApplication $application): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->user()->can('viewPii', $application), 403);

        $media = $application->getFirstMedia('cv');
        abort_unless($media !== null, 404);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties([
                'application_id' => $application->id,
                'ip' => request()->ip(),
            ])
            ->log('cv_downloaded');

        return redirect()->away(Storage::disk('private')->temporaryUrl(
            $media->getPathRelativeToRoot(),
            now()->addMinutes(15),
        ));
    }
}
