<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoCacheMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip binary file downloads or streamed responses
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if (method_exists($response, 'header')) {
            return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                            ->header('Pragma', 'no-cache')
                            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        if (isset($response->headers)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }
}