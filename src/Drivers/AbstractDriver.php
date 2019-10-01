<?php declare(strict_types=1);

namespace DoctrineFixtures\Drivers;

/**
 * Class AbstractDriver
 * @package DoctrineFixtures\Drivers
 */
abstract class AbstractDriver implements Driver
{
    /**
     * Table that should not be dropped
     */
    protected $protectedTables = [];

    /**
     * @param $table
     * @return bool
     */
    public function isProtectedTable($table): bool
    {
        return in_array($table, $this->protectedTables);
    }
}
