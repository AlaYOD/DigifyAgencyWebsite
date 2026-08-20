<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Models\RedirectMiss;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveManagedRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $this->normalizedPath($request);
        $redirect = Redirect::query()->where('from_path', $path)->where('is_active', true)->first();

        if ($redirect) {
            $redirect->increment('hits');
            $redirect->update(['last_hit_at' => now()]);

            return redirect()->away($redirect->to_url, $redirect->status_code);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 404 && ! $request->is('admin/*', 'build/*', 'storage/*')) {
            $miss = RedirectMiss::query()->firstOrCreate(['path' => $path], [
                'referrer' => $request->headers->get('referer'), 'user_agent' => $request->userAgent(), 'last_seen_at' => now(),
            ]);

            if (! $miss->wasRecentlyCreated) {
                $miss->increment('hits');
                $miss->update(['last_seen_at' => now()]);
            }
        }

        return $response;
    }

    private function normalizedPath(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? '/' : '/'.$path;
    }
}
