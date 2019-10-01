<?php declare(strict_types=1);

namespace DoctrineFixtures\Drivers;

use DoctrineFixtures\FixtureManager;

/**
 * Interface Driver
 * @package DoctrineFixtures\Drivers
 */
interface Driver
{
    /**
     * @return string
     */
    public function disableForeignKeyQuery(): string;

    /**
     * @return string
     */
    public function enableForeignKeyQuery(): string;

    /**
     * @param string $tableName
     * @return string
     */
    public function dropTableQuery(string $tableName): string;

    /**
     * @param string $tableName
     * @return string
     */
    public function truncateTableQuery(string $tableName): string;

    /**
     * @param $table
     * @return bool
     */
    public function isProtectedTable(string $table): bool;
}
