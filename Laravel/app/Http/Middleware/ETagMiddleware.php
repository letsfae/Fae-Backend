<?php 
namespace App\Http\Middleware;
use Closure;

class ETagMiddleware {
    /**
     * Implement Etag support
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get response
        $response = $next($request);
        
        // If this was a GET request...
        if ($request->isMethod('get')) {
            
            // Generate Etag
            $etag = md5($response->getContent());
            if($request->headers->has('ETag')){
                $requestEtag = $request->header('ETag');
                
                // Check to see if Etag has changed
                if($requestEtag && $requestEtag == $etag) {
                    $response->setNotModified();
                }
            }
               
            // Set Etag
            $response->setEtag($etag);
        }
        
        // Send response
        return $response;
    }
}