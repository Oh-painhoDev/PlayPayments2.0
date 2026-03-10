<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncreaseUploadLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set PHP upload and execution limits for large file uploads
        $uploadMaxFilesize = @ini_set('upload_max_filesize', '500M');
        $postMaxSize = @ini_set('post_max_size', '500M');
        $memoryLimit = @ini_set('memory_limit', '512M');
        $maxExecutionTime = @ini_set('max_execution_time', '300');
        $maxInputTime = @ini_set('max_input_time', '300');
        $maxFileUploads = @ini_set('max_file_uploads', '50');
        
        \Log::info('Upload limits set in middleware', [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ]);
        
        return $next($request);
    }
}
