<?php

namespace Tests;

use CaliforniaMountainSnake\LaravelDatabaseTestCase\AbstractDatabaseTestCase;

class DatabaseTestCase extends AbstractDatabaseTestCase
{
    /**
     * Load custom test dependencies.
     */
    protected function initDependencies(): void
    {

    }

    /**
     * Get the database connection for the root user.
     * You are not required to provide a connection for the actual root user.
     * User of this connection just must have privileges to create and delete databases.
     *
     * @return string
     */
    protected function getRootDbConnection(): string
    {
        return 'mysql';
    }

    /**
     * Get the database connection that contains the migrations table.
     *
     * @return string
     */
    protected function getMigrationDbConnection(): string
    {
        return 'mysql';
    }

    /**
     * Get the array with databases connections that will be replaced to the test ones.
     * All test databases will be deleted after tests, also in a case of exceptions or errors.
     *
     * @return string[]
     */
    protected function getMockedDbConnections(): array
    {
        return [
            'mysql',
        ];
    }

    /**
     * Do we need to create temporary databases for the mocked connections before the tests?
     * Sometimes you create databases directly in the migrations.
     *
     * @return bool
     */
    protected function isCreateMockedDatabases(): bool
    {
        return true;
    }

    /**
     * Get the absolute path to the /bootstrap/app.php.
     *
     * @return string
     */
    public function getAppFilePath(): string
    {
        return __DIR__ . '/../bootstrap/app.php';
    }
}
