<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Form $form, public ?int $submissionId, public array $submissionData) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New '.$this->form->getTranslation('name', 'en').' submission');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.form-submission-received');
    }
}
