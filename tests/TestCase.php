<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstNonSqliteTestDatabase();
        $this->registerSqliteMathFunctions();
    }

    /**
     * Safety net: tests use RefreshDatabase (migrate:fresh), which is
     * destructive. If the test connection ever resolves to a real database
     * (e.g. a container forcing DB_CONNECTION=mysql), abort immediately rather
     * than wipe live data.
     */
    protected function guardAgainstNonSqliteTestDatabase(): void
    {
        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();

        if ($driver !== 'sqlite' || ! in_array($database, [':memory:', '', null], true)) {
            throw new \RuntimeException(
                "Refusing to run tests against a non-SQLite database (driver={$driver}, database={$database}). ".
                'Tests must use in-memory SQLite. Check phpunit.xml DB_CONNECTION/DB_DATABASE and your environment.'
            );
        }
    }

    /**
     * Some PHP/SQLite builds ship without math functions (cos, sin, asin,
     * radians, ...) which the "stores near me" haversine query relies on.
     * Register PHP-backed equivalents so geo queries behave like MySQL in tests.
     */
    protected function registerSqliteMathFunctions(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        $pdo = $connection->getPdo();
        $pdo->sqliteCreateFunction('radians', fn ($v) => deg2rad((float) $v), 1);

        foreach (['cos', 'sin', 'asin', 'acos', 'sqrt'] as $fn) {
            $pdo->sqliteCreateFunction($fn, fn ($v) => $fn((float) $v), 1);
        }
    }
}
