<?php declare(strict_types=1);

namespace DoctrineFixtures\Drivers;

/**
 * Interface SQLLite
 * @package DoctrineFixtures\Drivers
 */
class SQLLite extends AbstractDriver
{
    /**
     * Table that should not be dropped
     */
    protected $protectedTables = [
        'sqlite_sequence'
    ];

    /**
     * @return string
     */
    public function disableForeignKeyQuery(): string
    {
        return 'PRAGMA foreign_keys = OFF';
    }

    /**
     * @return string
     */
    public function enableForeignKeyQuery(): string
    {
        return 'PRAGMA foreign_keys = ON';
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
        return 'DELETE FROM ' . $tableName;
    }
}
