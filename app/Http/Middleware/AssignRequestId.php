<?php

namespace App\Http\Middleware;

use App\Support\Http\RequestId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->headers->get('X-Request-Id') ?? (string) Str::uuid();

        if (strlen($id) > 128 || ! ctype_print($id)) {
            $id = (string) Str::uuid();
        }

        RequestId::set($id);
        $request->headers->set('X-Request-Id', $id);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
