<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        if (!$response instanceof Response) {
            return $response;
        }
        
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        if (str_contains($acceptEncoding, 'gzip')) {
            $content = $response->getContent();
            
            if ($content && strlen($content) > 1024) {
                $compressed = gzencode($content, 6);
                
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                    $response->headers->remove('Transfer-Encoding');
                }
            }
        }
        
        return $response;
    }
}
