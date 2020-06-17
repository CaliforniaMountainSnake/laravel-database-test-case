<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase;

use CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils\CreatesLaravelApplication;
use CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils\EchoLogger;
use CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils\SqlUtils;
use Illuminate\Foundation\Testing\TestCase as BaseLaravelTestCase;
use Illuminate\Support\Facades\Config;
use LogicException;
use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

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
    use LoggerAwareTrait;

    /**
     * Prefix of the temporary databases and users.
     */
    public const TEST_DBNAME_PREFIX = 'test_';

    /**
     * @var PDO
     */
    private $rootPdo;

    /**
     * @var bool
     */
    private static $isClassInitialized = false;

    /**
     * @inheritDoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->setLogger($this->createLogger());
    }

    /**
     * Load custom test dependencies.
     */
    abstract protected function initDependencies(): void;

    /**
     * Get the database connection for the root user.
     * You are not required to provide a connection for the actual root user.
     * User of this connection just must have privileges to create and delete databases.
     *
     * @return string
     */
    abstract protected function getRootDbConnection(): string;

    /**
     * Get the database connection that contains the migrations table.
     *
     * @return string
     */
    abstract protected function getMigrationDbConnection(): string;

    /**
     * Get the array with databases connections that will be replaced to the test ones.
     * All test databases will be deleted after tests, also in a case of exceptions or errors.
     *
     * @return string[]
     */
    abstract protected function getMockedDbConnections(): array;

    /**
     * Do we need to create temporary databases for the mocked connections before the tests?
     * Sometimes you create databases directly in the migrations.
     *
     * @return bool
     */
    abstract protected function isCreateMockedDatabases(): bool;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->createRootPdo();
        $this->mockDbConnections();
        $this->initDependencies();

        if (self::$isClassInitialized) {
            return;
        }
        $this->createDatabases();
        $this->registerShutdownFunction();
        $this->deployMigrations();
        $this->seedDatabases();
        self::$isClassInitialized = true;
    }

    /**
     * @return LoggerInterface
     */
    protected function createLogger(): LoggerInterface
    {
        return new EchoLogger();
    }

    /**
     * Do we need to seed databases?
     *
     * @return bool
     */
    protected function isSeed(): bool
    {
        return true;
    }

    /**
     * Do we need to execute migrations?
     *
     * @return bool
     */
    protected function isMigrate(): bool
    {
        return true;
    }

    /**
     * @return PDO
     */
    protected function getRootPdo(): PDO
    {
        return $this->rootPdo;
    }

    /**
     * @return PDO
     */
    protected function createRootPdo(): PDO
    {
        $dsn = Config::get('database.connections.' . $this->getRootDbConnection() . '.driver')
            . ':host=' . Config::get('database.connections.' . $this->getRootDbConnection() . '.host')
            . ';port=' . Config::get('database.connections.' . $this->getRootDbConnection() . '.port');
        $user = Config::get('database.connections.' . $this->getRootDbConnection() . '.username');
        $password = Config::get('database.connections.' . $this->getRootDbConnection() . '.password');

        $this->rootPdo = new PDO ($dsn, $user, $password);
        $this->rootPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->logger->debug('Root PDO created.');
        return $this->rootPdo;
    }

    /**
     * @param string $connection
     *
     * @return string
     */
    protected function resolveMockDatabase(string $connection): string
    {
        return static::TEST_DBNAME_PREFIX . $connection;
    }

    /**
     * @param string $connection
     *
     * @return string
     */
    protected function resolveMockUsername(string $connection): string
    {
        return static::TEST_DBNAME_PREFIX . $connection;
    }

    /**
     * @param string $connection
     *
     * @return string
     */
    protected function resolveMockPassword(string $connection): string
    {
        return static::TEST_DBNAME_PREFIX . $connection;
    }

    /**
     * Mock the databases connections params to the test ones.
     */
    private function mockDbConnections(): void
    {
        $connections = array_merge($this->getMockedDbConnections(), [$this->getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            $params = [
                'database.connections.' . $connection . '.database' => $this->resolveMockDatabase($connection),
                'database.connections.' . $connection . '.username' => $this->resolveMockUsername($connection),
                'database.connections.' . $connection . '.password' => $this->resolveMockPassword($connection),
            ];

            foreach ($params as $confKey => $confValue) {
                Config::set($confKey, $confValue);
                $this->logger->debug('Config key "' . $confKey . ' has been mocked to "' . $confValue . '"');
            }
        }
    }

    private function createDatabases(): void
    {
        $this->createMigrationsDatabase();
        $this->isCreateMockedDatabases() && $this->createMockedDatabases();
    }

    private function createMigrationsDatabase(): void
    {
        $this->logger->info('Migrations database: creating...');
        $status = $this->createDatabaseAndUserIfNotExists(
            $this->resolveMockDatabase($this->getMigrationDbConnection()),
            $this->resolveMockUsername($this->getMigrationDbConnection()),
            $this->resolveMockPassword($this->getMigrationDbConnection())
        );

        $this->logger->info('Migrations database: created.');
        $this->logger->debug('SQL status', $status);
        $this->logger->debug('DATABASES:', $this->showDatabases());
    }

    private function createMockedDatabases(): void
    {
        $this->logger->info('Mocked databases: creating...');
        $connections = array_merge($this->getMockedDbConnections(), [$this->getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            $status = $this->createDatabaseAndUserIfNotExists(
                $this->resolveMockDatabase($connection),
                $this->resolveMockUsername($connection),
                $this->resolveMockPassword($connection)
            );
            $this->logger->debug('SQL status of connection "' . $connection . '":', $status);
        }
        $this->logger->info('Mocked databases: created.');
        $this->logger->debug('DATABASES:', $this->showDatabases());
        $this->logTables($this->getMockedDbConnections());
    }

    private function deployMigrations(): void
    {
        if (!$this->isMigrate()) {
            return;
        }

        $this->logger->info('Migrations: executing...');
        $this->artisan('migrate');
        $this->logger->info('Migrations: executed.');

        $this->logger->debug('DATABASES:', $this->showDatabases());
        $this->logTables($this->getMockedDbConnections());
    }

    private function seedDatabases(): void
    {
        if (!$this->isSeed()) {
            return;
        }

        $this->logger->info('Database seeding: executing...');
        $this->artisan('db:seed');
        $this->logger->info('Database seeding: executed.');
        $this->logTables($this->getMockedDbConnections());
    }

    /**
     * @throws LogicException
     */
    private function dropDatabasesAndUsersIfExist(): void
    {
        $this->logger->info('Drop temp databases and users: executing...');
        $connections = array_merge($this->getMockedDbConnections(), [$this->getMigrationDbConnection()]);
        foreach ($connections as $connection) {
            $status = $this->dropDatabaseAndUserIfExists(
                $this->resolveMockDatabase($connection),
                $this->resolveMockUsername($connection)
            );
            $this->logger->debug('SQL status of connection "' . $connection . '":', $status);
        }
        $this->logger->info('Drop temp databases and users: executed.');

        $databases = $this->showDatabases();
        $this->logger->debug('DATABASES:', $databases);

        $isDatabasesDeleted = true;
        foreach ($connections as $connection) {
            $db = $this->resolveMockDatabase($connection);
            if (in_array($db, $databases, false)) {
                $isDatabasesDeleted = false;
                $this->logger->emergency('Database "' . $db . '" was not deleted!');
            }
        }

        if (!$isDatabasesDeleted) {
            throw new LogicException('Some databases were not deleted, see log.');
        }
    }

    /**
     * @param array $connections
     */
    private function logTables(array $connections): void
    {
        foreach ($connections as $connection) {
            $this->logger->debug('TABLES of "' . $this->resolveMockDatabase($connection) . '":',
                $this->showTables($connection));
        }
    }

    private function registerShutdownFunction(): void
    {
        register_shutdown_function(function () {
            $this->dropDatabasesAndUsersIfExist();
        });
    }
}
