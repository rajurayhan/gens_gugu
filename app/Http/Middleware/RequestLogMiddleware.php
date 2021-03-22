<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class RequestLogMiddleware
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
        $data = [
            'method'        => $request->method(),
            'url'           => $request->fullUrl(),
            'user_agent'    => $request->userAgent(),
            'ip_address'    => $request->ip(),
            'request_body'  => $request->input(),
        ];

        Log::channel('operationinfo')->info($data);

        return $next($request);
    }
}
