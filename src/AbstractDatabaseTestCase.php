<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase;

use CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils\CreatesLaravelApplication;
use CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils\SqlUtils;
use Illuminate\Foundation\Testing\TestCase as BaseLaravelTestCase;
use Illuminate\Support\Facades\Config;

/**
 * This is the class intended for the testing the anything that need the database connection in Laravel.
 * At first, the temporary database that contains the migrations table will be created.
 * Then, the migrations will be executed and the database seeding is performed.
 * All temp databases will be deleted after tests.
 */
abstract class AbstractDatabaseTestCase extends BaseLaravelTestCase
{
    use CreatesLaravelApplication;
    use SqlUtils;

    public const TEST_DBNAME_PREFIX = 'test_';

    /**
     * @var \PDO
     */
    private static $rootPdo;

    /**
     * @var bool
     */
    private static $isClassInitialized = false;


    /**
     * Load custom test dependencies.
     */
    abstract protected function initDependencies(): void;

    /**
     * Get the database connection for the root user.
     * @return string
     */
    abstract protected static function getRootDbConnection(): string;

    /**
     * Get the database connection that contains the migrations table.
     * @return string
     */
    abstract protected static function getMigrationDbConnection(): string;

    /**
     * Get the array with databases connections that will be replaced to the test ones.
     * All test databases will be deleted after tests, also in a case of exceptions or errors.
     *
     * @return string[]
     */
    abstract protected static function getMockedDbConnections(): array;


    public function setUp(): void
    {
        parent::setUp();
        $this->mockDbConnections();
        static::createRootPdo();
        $this->initDependencies();

        if (self::$isClassInitialized) {
            return;
        }
        static::createDatabases();
        static::registerShutdownFunction();
        $this->deployMigrations();
        $this->seedDatabases();
        self::$isClassInitialized = true;
    }

    /**
     * Do we need to seed databases?
     * @return bool
     */
    protected static function isSeed(): bool
    {
        return true;
    }

    /**
     * Do we need to execute migrations?
     * @return bool
     */
    protected static function isMigrate(): bool
    {
        return true;
    }

    /**
     * Do we need to create temporary databases for the mocked connections before the tests?
     * @return bool
     */
    protected static function isCreateMockedDatabases(): bool
    {
        return false;
    }

    /**
     * @return \PDO
     */
    protected static function getRootPdo(): \PDO
    {
        return self::$rootPdo;
    }

    /**
     * @return \PDO
     */
    protected static function createRootPdo(): \PDO
    {
        $dsn      = Config::get('database.connections.' . static::getRootDbConnection() . '.driver')
            . ':host=' . Config::get('database.connections.' . static::getRootDbConnection() . '.host')
            . ';port=' . Config::get('database.connections.' . static::getRootDbConnection() . '.port');
        $user     = Config::get('database.connections.' . static::getRootDbConnection() . '.username');
        $password = Config::get('database.connections.' . static::getRootDbConnection() . '.password');

        self::$rootPdo = new \PDO ($dsn, $user, $password);
        self::$rootPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return self::$rootPdo;
    }

    /**
     * Mock the databases connections params to the test ones.
     */
    private function mockDbConnections(): void
    {
        $connections = \array_merge(static::getMockedDbConnections(), [static::getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            Config::set('database.connections.' . $connection . '.database', static::TEST_DBNAME_PREFIX . $connection);
            Config::set('database.connections.' . $connection . '.username', static::TEST_DBNAME_PREFIX . $connection);
            Config::set('database.connections.' . $connection . '.password', static::TEST_DBNAME_PREFIX . $connection);
        }
    }

    private static function createDatabases(): void
    {
        static::createMigrationsDatabase();
        static::isCreateMockedDatabases() && static::createMockedDatabases();
    }

    private static function createMigrationsDatabase(): void
    {
        echo "\n#create_test_migrations_db...";
        static::createDatabaseAndUser(
            static::TEST_DBNAME_PREFIX . static::getMigrationDbConnection(),
            static::TEST_DBNAME_PREFIX . static::getMigrationDbConnection(),
            static::TEST_DBNAME_PREFIX . static::getMigrationDbConnection()
        );
        echo "ok#\n";
    }

    private static function createMockedDatabases(): void
    {
        echo "\n#create_test_mocked_dbs...";
        $connections = \array_merge(static::getMockedDbConnections(), [static::getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            static::createDatabaseAndUser(
                static::TEST_DBNAME_PREFIX . $connection,
                static::TEST_DBNAME_PREFIX . $connection,
                static::TEST_DBNAME_PREFIX . $connection
            );
        }

        echo "ok#\n";
    }

    private function deployMigrations(): void
    {
        if (!static::isMigrate()) {
            return;
        }

        echo '#migrate...';
        $this->artisan('migrate');
        echo "ok#\n";
    }

    private function seedDatabases(): void
    {
        if (!static::isSeed()) {
            return;
        }

        echo '#db:seed...';
        $this->artisan('db:seed');
        echo "ok#\n";
    }

    private static function dropDatabasesAndUsersIfExist(): void
    {
        echo "\n#drop_test_dbs_and_users...";
        $connections = \array_merge(static::getMockedDbConnections(), [static::getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            static::dropDatabaseAndUserIfExists(
                static::TEST_DBNAME_PREFIX . $connection,
                static::TEST_DBNAME_PREFIX . $connection
            );
        }
        echo "ok#\n";
    }

    private static function registerShutdownFunction(): void
    {
        \register_shutdown_function(static function () {
            static::dropDatabasesAndUsersIfExist();
        });
    }
}
