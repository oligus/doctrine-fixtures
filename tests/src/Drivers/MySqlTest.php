<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests\Src\Drivers;

use DoctrineFixtures\Drivers\MySql;
use DoctrineFixtures\Tests\TestCase;

/**
 * Class MySqlTest
 * @package Tests\Src\Drivers
 */
class MySqlTest extends TestCase
{
    public function testForeignKeyQueries()
    {
        $driver = new MySql();

        $this->assertEquals('SET FOREIGN_KEY_CHECKS = 0', $driver->disableForeignKeyQuery());
        $this->assertEquals('SET FOREIGN_KEY_CHECKS = 1', $driver->enableForeignKeyQuery());
    }

    public function testDropTableQuery()
    {
        $driver = new MySql();

        $this->assertEquals('DROP TABLE IF EXISTS accounts', $driver->dropTableQuery('accounts'));
    }

    public function testTruncateTableQuery()
    {
        $driver = new MySql();

        $this->assertEquals('TRUNCATE TABLE accounts', $driver->truncateTableQuery('accounts'));
    }

    public function testHasNoProtectedTables()
    {
        $driver = new MySql();

        $this->assertFalse($driver->isProtectedTable('accounts'));
    }
}
