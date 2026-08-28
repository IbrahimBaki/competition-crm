<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every test runs against a migrated database inside a transaction.
     *
     * This is declared centrally on purpose. The suite previously mixed
     * RefreshDatabase (migrate once, wrap each test in a transaction) with
     * DatabaseMigrations (migrate:fresh + rollback around *every* test), and
     * left ~47 files with no trait at all. Because DatabaseMigrations drops the
     * whole schema, any RefreshDatabase test that ran after one found its
     * tables gone ("Base table or view not found") and any DatabaseMigrations
     * test that ran after a RefreshDatabase test found them still there
     * ("Base table or view already exists"). Standardising on RefreshDatabase
     * removes both failure modes and the per-test migration cost.
     */
    use RefreshDatabase;
}
