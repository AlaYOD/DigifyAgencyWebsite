<?php

namespace App\Jobs;

use App\Mail\FormSubmissionReceived;
use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class DeliverFormSubmission implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(public int $formId, public ?int $submissionId, public array $payload) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(): void
    {
        $form = Form::withTrashed()->findOrFail($this->formId);

        foreach ($form->notify_emails ?? [] as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new FormSubmissionReceived($form, $this->submissionId, $this->payload));
            }
        }

        if (filled($form->webhook_url)) {
            $body = ['form' => $form->key, 'submission_id' => $this->submissionId, 'data' => $this->payload, 'submitted_at' => now()->toAtomString()];
            $encoded = json_encode($body, JSON_THROW_ON_ERROR);
            Http::timeout(10)->withHeaders([
                'X-Digify-Signature' => hash_hmac('sha256', $encoded, (string) config('app.key')),
                'Content-Type' => 'application/json',
            ])->withBody($encoded, 'application/json')->post($form->webhook_url)->throw();
        }
    }
}
