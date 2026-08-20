<?php

namespace App\Filament\Pages;

use App\Settings\SiteSettings as Settings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    public function mount(Settings $settings): void
    {
        $this->getSchema('form')?->fill([
            'site_name' => $settings->site_name,
            'site_tagline' => $settings->site_tagline,
            'default_locale' => $settings->default_locale,
            'fallback_locale' => $settings->fallback_locale,
            'contact_email' => $settings->contact_email,
            'social_links' => $settings->social_links,
            'analytics_enabled' => $settings->analytics_enabled,
            'maintenance_message' => $settings->maintenance_message,
            'cookie_consent_enabled' => $settings->cookie_consent_enabled,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Identity')->schema([
                TextInput::make('site_name')->required(), TextInput::make('contact_email')->email()->required(),
                Tabs::make('Localized copy')->tabs([
                    Tabs\Tab::make('English')->schema([TextInput::make('site_tagline.en'), Textarea::make('maintenance_message.en')]),
                    Tabs\Tab::make('العربية')->schema([TextInput::make('site_tagline.ar')->extraAttributes(['dir' => 'rtl']), Textarea::make('maintenance_message.ar')->extraAttributes(['dir' => 'rtl'])]),
                ])->columnSpanFull(),
            ])->columns(2),
            Section::make('Localization and privacy')->schema([
                TextInput::make('default_locale')->required()->in(['en', 'ar']), TextInput::make('fallback_locale')->required()->in(['en', 'ar']),
                Toggle::make('analytics_enabled'), Toggle::make('cookie_consent_enabled'),
            ])->columns(2),
            Repeater::make('social_links')->schema([TextInput::make('label')->required(), TextInput::make('url')->url()->required()])->columns(2),
        ]);
    }

    public function save(Settings $settings): void
    {
        $data = $this->getSchema('form')?->getState() ?? [];
        foreach ($data as $property => $value) {
            $settings->{$property} = $value;
        }
        $settings->save();
        activity('SiteSettings')->causedBy(auth()->user())->withProperties(['fields' => array_keys($data)])->log('Site settings updated');
        Notification::make()->title('Settings saved')->success()->send();
    }
}
