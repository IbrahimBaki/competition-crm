<?php

namespace App\Support\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

final class ApiResponse implements Responsable
{
    private mixed $data;

    private int $status;

    private array $meta;

    private array $links;

    private function __construct(mixed $data, int $status = 200, array $meta = [], array $links = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->meta = $meta;
        $this->links = $links;
    }

    public static function item(mixed $data, int $status = 200, array $meta = []): self
    {
        return new self($data, $status, array_merge($meta, [
            'request_id' => RequestId::current(),
        ]));
    }

    public static function collection(LengthAwarePaginator $page, array $meta = [], array $filters = [], ?string $sort = null): self
    {
        $baseMeta = [
            'request_id' => RequestId::current(),
            'page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'total_pages' => $page->lastPage(),
        ];

        if ($sort) {
            $baseMeta['sort'] = $sort;
        }

        if (! empty($filters)) {
            $baseMeta['filters'] = $filters;
        }

        $baseMeta = array_merge($baseMeta, $meta);

        $links = [
            'self' => $page->url($page->currentPage()),
            'first' => $page->url(1),
            'last' => $page->url($page->lastPage()),
            'prev' => $page->previousPageUrl(),
            'next' => $page->nextPageUrl(),
        ];

        return new self($page->items(), 200, $baseMeta, $links);
    }

    public static function noContent(): self
    {
        return new self(null, 204, []);
    }

    public function toResponse($request): JsonResponse
    {
        $body = [
            'data' => $this->data,
            'meta' => $this->meta,
        ];

        if (! empty($this->links)) {
            $body['links'] = $this->links;
        }

        return response()->json($body, $this->status)
            ->header('X-Request-Id', RequestId::current());
    }
}
