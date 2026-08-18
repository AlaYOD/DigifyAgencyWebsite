<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->segment(1);
        $locale = Locale::query()
            ->where('code', $requested)
            ->where('is_active', true)
            ->first();

        app()->setLocale($locale?->code ?? 'en');

        return $next($request);
    }
}
