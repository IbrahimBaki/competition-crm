<?php

namespace App\Support\Http\Errors;

use App\Support\Http\RequestId;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if ($e instanceof HasApiErrorCode) {
            return $this->renderApiError($e, $request);
        }

        if ($e instanceof ValidationException) {
            return $this->envelope(
                ErrorCode::ValidationFailed,
                'The given data was invalid.',
                $e->errors(),
                422,
            );
        }

        if ($e instanceof AuthenticationException) {
            return $this->envelope(
                ErrorCode::Unauthenticated,
                'Unauthenticated.',
                null,
                401,
            );
        }

        if ($e instanceof AuthorizationException) {
            return $this->envelope(
                ErrorCode::Unauthorized,
                'This action is unauthorized.',
                null,
                403,
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->envelope(
                ErrorCode::NotFound,
                'Resource not found.',
                null,
                404,
            );
        }

        if ($e instanceof ThrottleRequestsException) {
            $response = $this->envelope(
                ErrorCode::RateLimited,
                'Too many requests.',
                null,
                429,
            );

            if ($retryAfter = $e->getHeaders()['Retry-After'] ?? null) {
                $response->headers->set('Retry-After', $retryAfter);
            }

            foreach ($e->getHeaders() as $key => $value) {
                if (str_starts_with($key, 'X-RateLimit-')) {
                    $response->headers->set($key, $value);
                }
            }

            return $response;
        }

        return null;
    }

    private function renderApiError(HasApiErrorCode $e, Request $request): JsonResponse
    {
        $code = $e->errorCode();
        $status = $code->httpStatus();

        return $this->envelope(
            $code,
            $e->getMessage(),
            null,
            $status,
            $e->errorMeta(),
        );
    }

    private function envelope(
        ErrorCode $code,
        string $message,
        ?array $fieldErrors = null,
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $body = [
            'error' => [
                'code' => $code->value,
                'message' => $message,
                'request_id' => RequestId::current(),
            ],
        ];

        if ($fieldErrors !== null) {
            $body['error']['field_errors'] = $fieldErrors;
        }

        if (! empty($meta)) {
            $body['error']['meta'] = $meta;
        }

        return response()->json($body, $status)
            ->header('X-Request-Id', RequestId::current())
            ->header('Content-Language', App::getLocale());
    }
}
