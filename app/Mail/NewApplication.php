<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplication extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New application: '.$this->application->jobPosting->reference_code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-application',
            with: [
                'application' => $this->application,
                'adminUrl' => route('filament.admin.resources.job-applications.edit', ['record' => $this->application]),
            ],
        );
    }
}
