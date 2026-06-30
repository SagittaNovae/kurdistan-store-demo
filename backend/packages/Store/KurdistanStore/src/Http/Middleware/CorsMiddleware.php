<?php

namespace Store\KurdistanStore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origins = config('kurdistan-store.cors.allowed_origins', []);
        $origin = $request->header('Origin');

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders($this->headers($origin, $origins));
        }

        $response = $next($request);

        foreach ($this->headers($origin, $origins) as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    protected function headers(?string $origin, array $allowed): array
    {
        $allowOrigin = in_array($origin, $allowed, true) ? $origin : ($allowed[0] ?? '*');

        return [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN',
            'Access-Control-Allow-Credentials' => 'true',
        ];
    }
}
