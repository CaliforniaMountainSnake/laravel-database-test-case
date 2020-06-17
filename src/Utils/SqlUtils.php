<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils;

use Illuminate\Support\Facades\DB;
use PDO;

trait SqlUtils
{
    /**
     * @return PDO
     */
    abstract protected function getRootPdo(): PDO;

    /**
     * @param string $_database
     * @param string $_username
     * @param string $_password
     *
     * @return array Array with the status information about the queries.
     */
    protected function createDatabaseAndUserIfNotExists(string $_database, string $_username, string $_password): array
    {
        $status = [
            'database' => $_database,
            'username' => $_username,
        ];

        $status['create_database_if_not_exists'] = $this->getRootPdo()
            ->exec('CREATE DATABASE IF NOT EXISTS `' . $_database . '`');
        $status['create_user_if_not_exists'] = $this->getRootPdo()
            ->prepare('CREATE USER IF NOT EXISTS ? IDENTIFIED BY ?')
            ->execute([$_username, $_password]);

        $status['grant_privileges'] = $this->getRootPdo()
            ->exec('GRANT ALL ON `' . $_database . '`.* TO `' . $_username . '`');
        $status['flush_privileges'] = $this->getRootPdo()
            ->exec('FLUSH PRIVILEGES;');

        return $status;
    }

    /**
     * @param string $_database
     * @param string $_username
     *
     * @return array Array with the status information about the queries.
     */
    protected function dropDatabaseAndUserIfExists(string $_database, string $_username): array
    {
        $status = [
            'database' => $_database,
            'username' => $_username,
        ];

        $status['drop_database_if_exists'] = $this->getRootPdo()
            ->exec('DROP DATABASE IF EXISTS `' . $_database . '`');

        $status['drop_user_if_exists'] = $this->getRootPdo()
            ->prepare('DROP USER IF EXISTS ?')
            ->execute([$_username]);

        return $status;
    }

    /**
     * @return string[]
     */
    protected function showDatabases(): array
    {
        $dbs = $this->getRootPdo()->query('SHOW DATABASES;');
        return array_column($dbs->fetchAll(PDO::FETCH_NUM), 0);
    }

    /**
     * @param string $connection
     *
     * @return string[]
     */
    protected function showTables(string $connection): array
    {
        $tables = DB::connection($connection)->select('SHOW TABLE STATUS;');
        $result = [];

        array_walk($tables, static function ($value) use (&$result) {
            $result[((array)$value)['Name']] = ((array)$value)['Rows'];
        });

        return $result;
    }
}
