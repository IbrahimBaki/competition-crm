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

    public static function created(mixed $data, array $meta = []): self
    {
        return self::item($data, 201, $meta);
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

    /**
     * Collection response for an already-wrapped resource collection.
     *
     * collection() takes the paginator alone and emits raw models; use this
     * when the caller has wrapped the page in a JsonResource collection and
     * still needs the pagination meta and links.
     */
    public static function paginated(mixed $data, LengthAwarePaginator $page, array $meta = []): self
    {
        $baseMeta = array_merge([
            'request_id' => RequestId::current(),
            'page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'total_pages' => $page->lastPage(),
        ], $meta);

        $links = [
            'self' => $page->url($page->currentPage()),
            'first' => $page->url(1),
            'last' => $page->url($page->lastPage()),
            'prev' => $page->previousPageUrl(),
            'next' => $page->nextPageUrl(),
        ];

        return new self($data, 200, $baseMeta, $links);
    }

    public static function noContent(): self
    {
        return new self(null, 204, []);
    }

    /**
     * Single-resource 200 response. Alias of item() kept because controllers
     * across the SLA, AI, Automation, Reporting and Ticketing domains call it.
     */
    public static function ok(mixed $data, array $meta = []): self
    {
        return self::item($data, 200, $meta);
    }

    /**
     * Generic success response with an explicit status.
     *
     * Unlike item(), this accepts an already-built resource collection or a
     * plain array as well as a single resource, and degrades to a bodyless
     * 204 when $data is null.
     */
    public static function success(mixed $data = null, int $status = 200, array $meta = []): self
    {
        if ($data === null && $status === 204) {
            return self::noContent();
        }

        return self::item($data, $status, $meta);
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
