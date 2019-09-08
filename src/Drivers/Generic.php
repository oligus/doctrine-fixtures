<?php declare(strict_types=1);

namespace DoctrineFixtures\Drivers;

/**
 * Class Generic
 * @package DoctrineFixtures\Drivers
 */
class Generic extends AbstractDriver
{
    public function disableForeignKeyQuery(): string
    {
        return '';
    }

    public function enableForeignKeyQuery(): string
    {
        return '';
    }

    public function dropTableQuery(string $tableName): string
    {
        return 'DROP TABLE ' . $tableName;
    }

    public function truncateTableQuery(string $tableName): string
    {
        return 'DELETE FROM ' . $tableName;
    }
}
