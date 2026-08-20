<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_name;

    public array $site_tagline;

    public string $default_locale;

    public string $fallback_locale;

    public string $contact_email;

    public array $social_links;

    public bool $analytics_enabled;

    public array $maintenance_message;

    public bool $cookie_consent_enabled;

    public static function group(): string
    {
        return 'site';
    }
}
