<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('submissions.view');
    }

    public function view(User $user, FormSubmission $submission): bool
    {
        return $user->can('submissions.view') && $user->can('view', $submission->form);
    }

    public function export(User $user, FormSubmission $submission): bool
    {
        return $user->can('submissions.export') && $user->can('view', $submission->form);
    }

    public function delete(User $user, FormSubmission $submission): bool
    {
        return $user->can('submissions.delete') && $user->can('view', $submission->form);
    }
}
