# laravel-database-test-case

[![Latest Version on Packagist](https://img.shields.io/packagist/v/californiamountainsnake/laravel-database-test-case.svg)](https://packagist.org/packages/californiamountainsnake/laravel-database-test-case)
[![Total Downloads](https://img.shields.io/packagist/dt/californiamountainsnake/laravel-database-test-case.svg)](https://packagist.org/packages/californiamountainsnake/laravel-database-test-case)
[![License](https://img.shields.io/github/license/californiamountainsnake/laravel-database-test-case.svg)](LICENSE.md)
[![Build Status](https://travis-ci.com/CaliforniaMountainSnake/laravel-database-test-case.svg?branch=master)](https://travis-ci.com/CaliforniaMountainSnake/laravel-database-test-case)


Do you want to test your models, repositories, controllers on the real database?
This library can help you!
It creates temporary user and databases, perform your migrations and seeds databases.
**And then always deletes them when tests have been finished**, even if there were exceptions and errors.
And, of course, your usual database will not be affected.

## Compatibility
This library supports `PHP ^7.1` and a lot of versions of Laravel: `^5.5`, `^6.0`, `^7.0`.
The main condition: you must to have a mysql user that have privileges to create and delete other users and databases (for creation and deletion temp user and databases).


## Install:
### Require this package with Composer
Install this package through [Composer](https://getcomposer.org/).
Edit your project's `composer.json` file to require `californiamountainsnake/laravel-database-test-case`:
```json
{
    "name": "yourproject/yourproject",
    "type": "project",
    "require": {
        "php": "^7.2",
        "californiamountainsnake/laravel-database-test-case": "*"
    }
}
```
and run `composer update`

### or
run this command in your command line:
```bash
composer require californiamountainsnake/laravel-database-test-case
```

## Usage:
Extend the `AbstractDatabaseTestCase` class and implement the abstract methods:
```php
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

```

And that's it! Now you can extend your database tests from your `DatabaseTestCase` and execute queries to the database using your usual models:

```php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Tests\DatabaseTestCase;

class SimpleDatabaseTest extends DatabaseTestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGetUsers(): void
    {
        $users = DB::table('users')->get()->toArray(); // Gets users from the temp seeded database.
        $this->assertTrue(count($users) > 0);
    }
}
```
