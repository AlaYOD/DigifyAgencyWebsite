<?php

namespace App\Observers;

use App\Models\JobPosting;
use Illuminate\Support\Facades\Cache;

class JobPostingObserver
{
    public function saved(JobPosting $posting): void
    {
        Cache::tags(['careers'])->flush();
    }
}
