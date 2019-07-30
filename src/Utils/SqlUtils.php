<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils;

trait SqlUtils
{
    /**
     * @return \PDO
     */
    abstract protected static function getRootPdo(): \PDO;

    /**
     * @param string $_database
     * @param string $_username
     * @param string $_password
     */
    private static function createDatabaseAndUser(string $_database, string $_username, string $_password): void
    {
        static::getRootPdo()->exec('CREATE DATABASE `' . $_database . '`');
        static::getRootPdo()->prepare('CREATE USER ? IDENTIFIED BY ?')->execute([$_username, $_password]);

        static::getRootPdo()->exec('GRANT ALL ON `' . $_database . '`.* TO `' . $_username . '`');
        static::getRootPdo()->exec('FLUSH PRIVILEGES;');
    }

    /**
     * @param string $_database
     * @param string $_username
     */
    private static function dropDatabaseAndUserIfExists(string $_database, string $_username): void
    {
        static::getRootPdo()->exec('DROP DATABASE IF EXISTS `' . $_database . '`');

        static::getRootPdo()->prepare('DROP USER IF EXISTS ?')
            ->execute([$_username]);
    }
}
