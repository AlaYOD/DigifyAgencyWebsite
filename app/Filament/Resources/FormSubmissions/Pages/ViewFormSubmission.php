<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\FormSubmission;
use Filament\Resources\Pages\ViewRecord;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function afterFill(): void
    {
        if ($this->record instanceof FormSubmission && ! $this->record->read_at) {
            $this->record->update(['read_at' => now()]);
        }
    }
}
