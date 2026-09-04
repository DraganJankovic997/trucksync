<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->guardAgainstUnsafeDatabase();

        parent::setUp();
    }

    private function guardAgainstUnsafeDatabase(): void
    {
        $environment = $this->environmentValue('APP_ENV');
        $connection = $this->environmentValue('DB_CONNECTION');
        $database = $this->environmentValue('DB_DATABASE');

        if ($environment !== 'testing') {
            throw new RuntimeException('Tests must run with APP_ENV=testing.');
        }

        if ($connection !== 'pgsql') {
            throw new RuntimeException('Tests must run against PostgreSQL.');
        }

        if (! is_string($database) || ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                'Refusing to run tests against a non-test database.'
            );
        }
    }

    private function environmentValue(string $key): ?string
    {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return null;
        }

        return (string) $value;
    }
}
