# laravel-database-test-case
This is the class intended for the testing the anything that need the database connection in Laravel.
At first, the temporary database that contains the migrations table will be created.
Then, the migrations will be executed and the database seeding is performed.
All temp databases will be deleted after tests.

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
1. Extend the AbstractDatabaseTestCase class and realise the abstract methods.
Example:
```php
<?php
class DatabaseTestCase extends AbstractDatabaseTestCase
{
    /**
     * Load custom test dependencies.
     */
    protected function initDependencies(): void
    {
        $this->someClass = app()->make(SomeClass::class);
        $this->secondClass = app()->make(SecondClass::class);
    }
    
    /**
     * Get the database connection for the root user.
     * @return string
     */
    protected static function getRootDbConnection(): string
    {
        return 'mysql_root';
    }

    /**
     * Get the database connection that contains the migrations table.
     * @return string
     */
    protected static function getMigrationDbConnection(): string
    {
        return 'mysql_migrations';
    }

    /**
     * Get the array with databases connections that will be replaced to the test ones.
     * All test databases will be deleted after tests, also in a case of exceptions or errors.
     *
     * @return string[]
     */
    protected static function getMockedDbConnections(): array
    {
        return [
            'mysql_application',
            'mysql_extra_database',
        ];
    }

    /**
     * Get the absolute path to the /bootstrap/app.php.
     * @return string
     */
    public function getAppFilePath(): string
    {
        return __DIR__ . '/../bootstrap/app.php';
    }
}
```
2. Just extend your database test classes from your DatabaseTestCase and you will have access to the database and migrations.
