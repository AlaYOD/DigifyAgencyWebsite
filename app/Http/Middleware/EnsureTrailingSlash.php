<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_ends_with($path, '/') && preg_match('#^(?:/ar)?/careers(?:/[^/]*)?$#', $path) === 1) {
            $target = $path.'/';

            if ($request->getQueryString() !== null) {
                $target .= '?'.$request->getQueryString();
            }

            return new RedirectResponse($target, 301);
        }

        return $next($request);
    }
}
