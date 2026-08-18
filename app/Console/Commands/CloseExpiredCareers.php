<?php

namespace App\Console\Commands;

use App\Enums\JobStatus;
use App\Models\JobPosting;
use Illuminate\Console\Command;

class CloseExpiredCareers extends Command
{
    protected $signature = 'careers:close-expired';

    protected $description = 'Close published vacancies whose closing time has passed';

    public function handle(): int
    {
        $count = JobPosting::query()
            ->where('status', JobStatus::PUBLISHED)
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->update(['status' => JobStatus::CLOSED]);

        $this->info("Closed {$count} expired vacancy(ies).");

        return self::SUCCESS;
    }
}
