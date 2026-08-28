<?php

namespace App\Http\Middleware;

use App\Support\Http\RequestId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnforceIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        $idempotencyKey = $request->headers->get('Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $scope = $request->user()?->getAuthIdentifier()
            ? 'user:'.$request->user()->getAuthIdentifier()
            : 'ip:'.$request->ip();

        $fingerprint = hash('sha256', $request->method().$request->path().$request->getContent());

        $stored = DB::table('idempotency_keys')
            ->where('scope', $scope)
            ->where('key', $idempotencyKey)
            ->where('expires_at', '>', now())
            ->first();

        if ($stored) {
            if ($stored->request_fingerprint !== $fingerprint) {
                return response()->json([
                    'error' => [
                        'code' => 'idempotency_key_conflict',
                        'message' => 'Idempotency key used with different request body.',
                        'request_id' => RequestId::current(),
                    ],
                ], 409)->header('X-Request-Id', RequestId::current());
            }

            return response(
                $stored->response_body,
                $stored->status_code,
                json_decode($stored->response_headers, true) ?? []
            )->header('X-Request-Id', RequestId::current());
        }

        $response = $next($request);

        if ($response->getStatusCode() < 500) {
            DB::table('idempotency_keys')->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'key' => $idempotencyKey,
                'scope' => $scope,
                'request_fingerprint' => $fingerprint,
                'status_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
                'response_headers' => json_encode($response->headers->all()),
                'created_at' => now(),
                'expires_at' => now()->addHours(24),
            ]);
        }

        return $response;
    }
}
