<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GzipResponseMiddleware
{
    /**
     * Handle an incoming request and apply Gzip compression for payloads > 1KB.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip non-standard responses like streams or binary files
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // Check if compression is already handled or not supported
        if (!function_exists('gzencode') || ini_get('zlib.output_compression')) {
            return $response;
        }

        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Check content type and size
        $content = $response->getContent();
        if ($content === false || strlen($content) < 1024) {
            return $response; // Don't compress tiny payloads
        }

        // Don't re-compress if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        $compressed = gzencode($content, 5); // Level 5 gives optimal CPU-to-Compression ratio
        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->set('Content-Length', (string)strlen($compressed));
        }

        return $response;
    }
}
