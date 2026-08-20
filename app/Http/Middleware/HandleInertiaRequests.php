<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use App\Models\Menu;
use App\Services\MenuResolver;
use App\Settings\SiteSettings;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share($request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn (): string => app()->getLocale(),
            'direction' => fn (): string => Locale::where('code', app()->getLocale())->value('direction') ?? 'ltr',
            'locales' => fn () => Locale::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['code', 'native_name', 'direction']),
            'settings' => fn (): array => [
                'site_name' => app(SiteSettings::class)->site_name,
                'contact_email' => app(SiteSettings::class)->contact_email,
            ],
            'menus' => fn (): array => Menu::query()->get()->mapWithKeys(fn (Menu $menu): array => [
                $menu->key => app(MenuResolver::class)->resolve($menu, app()->getLocale()),
            ])->all(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'form_success' => fn () => $request->session()->get('form_success'),
            ],
        ];
    }
}
