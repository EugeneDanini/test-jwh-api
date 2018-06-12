<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequestResponse {

    public function handle(Request $request, Closure  $next)
    {
        $data = [
            'request_method' => $request->getMethod(),
            'request_post' => $request->post(),
            'request_url' => $request->fullUrl(),
            'request_headers' => $request->headers->all(),
        ];
        Log::info('request', $data);

        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        $data = [
            'route' => $request->route(),
            'response_code' => $response->getStatusCode(),
            'response_body' => $response->getContent(),
        ];
        Log::info('response', $data);
    }

}