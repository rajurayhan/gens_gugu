<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class ResponseLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $data = [
            'status'        => $response->status(),
            'content'       => $response->content(),
        ];

        Log::channel('operationinfo')->info($data);

        return $response;
    }
}
