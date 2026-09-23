<?php declare(strict_types=1);

namespace DoctrineFixtures\Drivers;

/**
 * Class MySql
 * @package DoctrineFixtures\Drivers
 */
class MySql extends AbstractDriver
{
    /**
     * @return string
     */
    public function disableForeignKeyQuery(): string
    {
        return 'SET FOREIGN_KEY_CHECKS = 0';
    }

    /**
     * @return string
     */
    public function enableForeignKeyQuery(): string
    {
        return 'SET FOREIGN_KEY_CHECKS = 1';
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function dropTableQuery(string $tableName): string
    {
        return 'DROP TABLE IF EXISTS ' . $tableName;
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function truncateTableQuery(string $tableName): string
    {
        return 'TRUNCATE TABLE ' . $tableName;
    }
}
