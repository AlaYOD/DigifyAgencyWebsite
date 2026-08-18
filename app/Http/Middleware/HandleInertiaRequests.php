<?php

namespace App\Http\Middleware;

use App\Models\Locale;
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
            'settings' => [
                'site_name' => 'Digify',
                'contact_email' => 'hello@digify.test',
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
