<?php

namespace App\Console\Commands;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredFormSubmissions extends Command
{
    protected $signature = 'forms:purge-expired {--dry-run}';

    protected $description = 'Purge form submissions and private uploads after each form retention period';

    public function handle(): int
    {
        $purged = 0;

        Form::withTrashed()->with('fields')->each(function (Form $form) use (&$purged): void {
            $query = $form->submissions()->where('created_at', '<', now()->subDays($form->retention_days));
            $fileKeys = $form->fields->where('type', FormFieldType::FILE)->pluck('key');

            $query->chunkById(100, function ($submissions) use ($fileKeys, &$purged): void {
                foreach ($submissions as $submission) {
                    if (! $submission instanceof FormSubmission) {
                        continue;
                    }

                    foreach ($fileKeys as $key) {
                        $path = data_get($submission->data, $key);
                        if (is_string($path) && filled($path) && ! $this->option('dry-run')) {
                            Storage::disk('private')->delete($path);
                        }
                    }

                    if (! $this->option('dry-run')) {
                        $submission->delete();
                    }
                    $purged++;
                }
            });
        });

        $this->info(($this->option('dry-run') ? 'Would purge' : 'Purged')." {$purged} submission(s).");

        return self::SUCCESS;
    }
}
