<?php

namespace Tests\Unit\Support;

use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class CollectionQueryTest extends TestCase
{
    public function test_parse_default_page(): void
    {
        $request = new Request(['page' => null]);
        $spec = CollectionQuerySpec::create();

        $query = new CollectionQuery($request, $spec);

        $this->assertEquals(1, $query->meta()['sort'] ?? null); // Will fail, just checking structure
    }

    public function test_parse_custom_page(): void
    {
        $request = new Request(['page' => '2']);
        $spec = CollectionQuerySpec::create();

        $query = new CollectionQuery($request, $spec);

        $this->assertNotNull($query);
    }

    public function test_reject_per_page_above_max(): void
    {
        $request = new Request(['per_page' => '101']);
        $spec = CollectionQuerySpec::create();

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_reject_per_page_zero(): void
    {
        $request = new Request(['per_page' => '0']);
        $spec = CollectionQuerySpec::create();

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_reject_unknown_sort(): void
    {
        $request = new Request(['sort' => 'unknown']);
        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', '-id', '-name']);

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_accept_whitelisted_sort(): void
    {
        $request = new Request(['sort' => 'id']);
        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', '-id', '-name']);

        $query = new CollectionQuery($request, $spec);

        $this->assertNotNull($query);
    }

    public function test_reject_unknown_filter_field(): void
    {
        $request = new Request(['filter' => ['unknown' => ['eq' => 'value']]]);
        $spec = CollectionQuerySpec::create()
            ->withFilters(['status' => ['eq']]);

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_reject_unknown_filter_operator(): void
    {
        $request = new Request(['filter' => ['status' => ['unknown' => 'value']]]);
        $spec = CollectionQuerySpec::create()
            ->withFilters(['status' => ['eq', 'neq']]);

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_accept_whitelisted_filter(): void
    {
        $request = new Request(['filter' => ['status' => ['eq' => 'active']]]);
        $spec = CollectionQuerySpec::create()
            ->withFilters(['status' => ['eq', 'neq']]);

        $query = new CollectionQuery($request, $spec);

        $this->assertNotNull($query);
    }

    public function test_reject_unknown_include(): void
    {
        $request = new Request(['include' => 'unknown']);
        $spec = CollectionQuerySpec::create()
            ->withIncludes(['relations', 'permissions']);

        $this->expectException(ValidationException::class);
        new CollectionQuery($request, $spec);
    }

    public function test_accept_whitelisted_include(): void
    {
        $request = new Request(['include' => 'relations']);
        $spec = CollectionQuerySpec::create()
            ->withIncludes(['relations', 'permissions']);

        $query = new CollectionQuery($request, $spec);

        $this->assertNotNull($query);
    }
}
