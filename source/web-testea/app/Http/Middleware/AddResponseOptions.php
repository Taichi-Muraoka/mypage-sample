<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddResponseOptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // レスポンスヘッダに X-Frame-Options を指定する
        $response->headers->set('X-Frame-Options', 'deny');
        // レスポンスヘッダに X-Content-Type-Options を指定する
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
