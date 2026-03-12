<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key') ?? $request->query('api_key');
        if ($key !== config('app.api_key')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key',
                'errors' => [],
            ], 401);
        }
        return $next($request);
    }
}
