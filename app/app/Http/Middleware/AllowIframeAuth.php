<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class AllowIframeAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add headers to allow embedding in iframe and enable cross-origin cookies
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Origin', $request->header('Origin', '*'));
        
        // Modify cookies to add Partitioned attribute for Chrome
        $cookies = $response->headers->getCookies();
        
        foreach ($cookies as $cookie) {
            $cookieName = $cookie->getName();
            $cookieValue = $cookie->getValue();
            $expires = $cookie->getExpiresTime();
            $path = $cookie->getPath();
            $domain = $cookie->getDomain();
            $secure = false; // Must be false for Replit
            $httpOnly = $cookie->isHttpOnly();
            $sameSite = 'none';
            
            // Create custom cookie string with Partitioned attribute
            $cookieString = sprintf(
                '%s=%s; expires=%s; Max-Age=%d; path=%s; samesite=%s%s',
                $cookieName,
                $cookieValue,
                gmdate('D, d M Y H:i:s T', $expires),
                $expires - time(),
                $path,
                $sameSite,
                $httpOnly ? '; httponly' : ''
            );
            
            // Add Partitioned attribute (CHIPS - Cookies Having Independent Partitioned State)
            $cookieString .= '; partitioned';
            
            // Remove old cookie and add new one with Partitioned
            $response->headers->remove('Set-Cookie', $cookie->__toString());
            $response->headers->set('Set-Cookie', $cookieString, false);
        }
        
        return $response;
    }
}
