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

class ApplicationReceived extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application)
    {
        $this->locale($application->locale);
    }

    public function envelope(): Envelope
    {
        $arabic = $this->application->locale === 'ar';

        return new Envelope(subject: $arabic ? 'تم استلام طلب التوظيف' : 'Application received');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-received',
            with: [
                'application' => $this->application,
                'vacancyTitle' => $this->vacancyTitle(),
            ],
        );
    }

    public function vacancyTitle(): string
    {
        $translations = $this->application->jobPosting->getTranslations('title');
        $locale = $this->application->locale ?: config('app.locale');
        $defaultLocale = config('app.fallback_locale', config('app.locale'));

        return filled($translations[$locale] ?? null)
            ? $translations[$locale]
            : ($translations[$defaultLocale] ?? '');
    }
}
